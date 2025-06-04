<?php

namespace App\Filament\Resources\PedidoResource\Pages;

use App\Filament\Resources\PedidoResource;
use App\Models\Pedido; 
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $userId = Auth::id();
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

        // Obtener la conexión específica para el 'inserter'
        $dbInserterConnection = DB::connection('mysql_inserter');

        try {
            $dbInserterConnection->statement(
                "CALL sp_crear_pedido(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @success, @message, @pedido_id)",
                [
                    $userId, $repartidorId, $metodoPagoId, $promocionId,
                    $direccionEntrega, $puntoEntrega, $estadoPedido,
                    $fechaEstimada, $notasCliente, $codigoSeguimiento,
                    $detallesJson
                ]
            );

            $result = $dbInserterConnection->selectOne("SELECT @success AS success, @message AS message, @pedido_id AS pedido_id");
            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Pedido creado exitosamente!')
                    ->success()
                    ->send();

                $pedido = Pedido::find($result->pedido_id);
                if (!$pedido) {
                    Notification::make()->title('Error de Sincronización')
                        ->body('El pedido se creó en la BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()->send();
                    $this->halt();
                    return new Pedido(); 
                }
                return $pedido;
            } else {
                Notification::make()->title('Error al Crear Pedido')
                    ->body($result->message ?: 'No se pudo guardar el pedido (según SP).')
                    ->danger()->send();
                $this->halt();
                return new Pedido(); 
            }
        } catch (\Exception $e) {
            report($e); 
            Notification::make()->title('Error Inesperado de Base de Datos')
                ->body('Ocurrió un problema técnico: ' . $e->getMessage())
                ->danger()->send();
            $this->halt();
            return new Pedido();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Para desactivar la notificación por defecto de Filament y evitar duplicados
    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }
}