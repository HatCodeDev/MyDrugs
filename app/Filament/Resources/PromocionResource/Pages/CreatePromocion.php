<?php

namespace App\Filament\Resources\PromocionResource\Pages;

use App\Filament\Resources\PromocionResource;
use App\Models\Promocion; // Modelo Eloquent base
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
// use Illuminate\Support\Facades\Log; // Para debugging

class CreatePromocion extends CreateRecord
{
    protected static string $resource = PromocionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $codigoPromocion = $data['codigo_promocion'];
        $descripcion = $data['descripcion'] ?? null;
        $tipoDescuento = $data['tipo_descuento'];
        $valorDescuento = $data['valor_descuento'];
        $fechaInicio = $data['fecha_inicio'];
        $fechaFin = $data['fecha_fin'] ?? null;
        $usosMaximosGlobal = $data['usos_maximos_global'] ?? null;
        $usosMaximosPorUsuario = $data['usos_maximos_por_usuario'] ?? null; // El SP tiene default, pero el form puede enviar null
        $activo = $data['activo'] ?? true;
        $aplicableACategoriaId = $data['aplicable_a_categoria_id'] ?? null;
        $aplicableAProductoId = $data['aplicable_a_producto_id'] ?? null;
        $montoMinimoPedido = $data['monto_minimo_pedido'] ?? null;

        try {
            DB::statement(
                "CALL sp_crear_promocion(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @success, @message, @promocion_id)",
                [
                    $codigoPromocion,
                    $descripcion,
                    $tipoDescuento,
                    $valorDescuento,
                    $fechaInicio,
                    $fechaFin,
                    $usosMaximosGlobal,
                    $usosMaximosPorUsuario,
                    $activo,
                    $aplicableACategoriaId,
                    $aplicableAProductoId,
                    $montoMinimoPedido
                ]
            );

            $result = DB::selectOne("SELECT @success AS success, @message AS message, @promocion_id AS promocion_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Promoción creada exitosamente!')
                    ->success()
                    ->send();

                $promocion = Promocion::find($result->promocion_id);

                if (!$promocion) {
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('La promoción se creó en BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()
                        ->send();
                    $this->halt();
                    return new Promocion();
                }
                return $promocion;
            } else {
                Notification::make()
                    ->title('Error al crear promoción')
                    ->body($result->message ?: 'No se pudo guardar la promoción (según SP).')
                    ->danger()
                    ->send();
                $this->halt();
                return new Promocion();
            }
        } catch (\Exception $e) {
            report($e);
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al guardar la promoción: ' . $e->getMessage())
                ->danger()
                ->send();
            $this->halt();
            return new Promocion();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return null; // Deshabilitamos la notificación por defecto
    }
}