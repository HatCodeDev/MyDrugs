<?php

namespace App\Filament\Resources\PromocionResource\Pages;

use App\Filament\Resources\PromocionResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Actions;

class EditPromocion extends EditRecord
{
    protected static string $resource = PromocionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // Podrías añadirla y personalizarla para usar tu SP
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $promocionId = $record->getKey();
        $codigoPromocion = $data['codigo_promocion'];
        $descripcion = $data['descripcion'] ?? null;
        $tipoDescuento = $data['tipo_descuento'];
        $valorDescuento = $data['valor_descuento'];
        $fechaInicio = $data['fecha_inicio'];
        $fechaFin = $data['fecha_fin'] ?? null;
        $usosMaximosGlobal = $data['usos_maximos_global'] ?? null;
        $usosMaximosPorUsuario = $data['usos_maximos_por_usuario'] ?? null;
        $activo = $data['activo'] ?? true;
        $aplicableACategoriaId = $data['aplicable_a_categoria_id'] ?? null;
        $aplicableAProductoId = $data['aplicable_a_producto_id'] ?? null;
        $montoMinimoPedido = $data['monto_minimo_pedido'] ?? null;

        try {
            DB::statement(
                "CALL sp_actualizar_promocion(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @success, @message)",
                [
                    $promocionId,
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

            $result = DB::selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Promoción actualizada exitosamente!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al actualizar promoción')
                    ->body($result->message ?: 'No se pudo actualizar la promoción (según SP).')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            report($e);
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al actualizar la promoción: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return $record->refresh(); // Importante para actualizar el modelo en Filament
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null; // Deshabilitamos la notificación por defecto
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}