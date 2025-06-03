<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class EditPedido extends EditRecord
{
    protected static string $resource = PedidoResource::class;
    /**
     * Este método se llama cuando Filament intenta rellenar el formulario con los datos
     * del registro que se está editando.
     * Si tu Repeater 'detalles' no se llena automáticamente con los datos de la relación
     * $this->record->detalles (asumiendo que $this->record es una instancia de Pedido
     * y tiene una relación hasMany llamada 'detalles'), puedes descomentar y adaptar esto.
     * Normalmente, Filament V3 es bueno manejando esto si las convenciones se siguen.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record && method_exists($this->record, 'detalles')) {
            $this->record->loadMissing('detalles');

            $data['detalles'] = $this->record->detalles->map(function ($detalleItem) {
                return [
                    'producto_id' => $detalleItem->producto_id,
                    'cantidad' => $detalleItem->cantidad,
                    'precio_unitario_en_pedido' => $detalleItem->precio_unitario_en_pedido,
                ];
            })->toArray();
        }
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        $userId = $data['user_id'] ?? $record->user_id; 
        $repartidorId = $data['repartidor_id'] ?? null;
        $metodoPagoId = $data['metodo_pago_id'];
        $promocionId = $data['promocion_id'] ?? null;
        $direccionEntrega = $data['direccion_entrega_cifrada'] ?? null;
        $puntoEntrega = $data['punto_entrega_especial'] ?? null;
        $estadoPedido = $data['estado_pedido'] ?? 'PENDIENTE'; 
        $fechaEstimada = $data['fecha_estimada_entrega'] ?? null;
        $notasCliente = $data['notas_cliente'] ?? null;
        $codigoSeguimiento = $data['codigo_seguimiento'] ?? null;

        $detallesArray = $data['detalles'] ?? [];

        $detallesJson = json_encode(array_map(function ($detalle) {
            return [
                'producto_id' => $detalle['producto_id'],
                'cantidad' => $detalle['cantidad'],
                'precio_unitario_en_pedido' => $detalle['precio_unitario_en_pedido'],
            ];
        }, $detallesArray));

        try {
            DB::statement(
                "CALL sp_actualizar_pedido(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @success, @message)",
                [
                    $record->getKey(), 
                    $userId,
                    $repartidorId,
                    $metodoPagoId,
                    $promocionId,
                    $direccionEntrega,
                    $puntoEntrega,
                    $estadoPedido,
                    $fechaEstimada,
                    $notasCliente,
                    $codigoSeguimiento,
                    $detallesJson
                ]
            );

            $result = DB::selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Pedido actualizado exitosamente!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al Actualizar Pedido')
                    ->body($result->message ?: 'No se pudo actualizar el pedido (según SP).')
                    ->danger()
                    ->send();
                // Considera si quieres detener la ejecución o permitir que Filament continúe.
                // $this->halt(); // Si el error del SP es crítico.
            }
        } catch (\Exception $e) {
            
            report($e);
            Notification::make()->title('Error Inesperado de Base de Datos')
                ->body('Ocurrió un problema técnico al actualizar: ' . $e->getMessage())
                ->danger()->send();
            // $this->halt(); // Si el error es crítico.
        }

        // Importante: Refresca el modelo para que Filament tenga los datos actualizados
        // después de que el SP haya hecho los cambios.
        return $record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Para desactivar la notificación por defecto de Filament y evitar duplicados
    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }
}