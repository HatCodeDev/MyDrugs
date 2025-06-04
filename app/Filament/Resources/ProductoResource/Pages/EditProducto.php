<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Actions;

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // Puedes añadirla aquí si la personalizas para usar tu SP
            // Actions\ViewAction::make(), // Si tienes una página de vista de solo lectura
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $productoId = $record->getKey();
        $categoriaId = $data['categoria_id'];
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'];
        $precioUnitario = $data['precio_unitario'];
        $unidadMedida = $data['unidad_medida'];
        $activo = $data['activo'] ?? true;
        $dbInserterConnection = DB::connection('mysql_inserter'); 
        try {
            $dbInserterConnection->statement( 
                "CALL sp_actualizar_producto(?, ?, ?, ?, ?, ?, ?, @success, @message)",
                [
                    $productoId,
                    $categoriaId,
                    $nombre,
                    $descripcion,
                    $precioUnitario,
                    $unidadMedida,
                    $activo
                ]
            );

            $result = $dbInserterConnection->selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Producto actualizado exitosamente!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al actualizar producto')
                    ->body($result->message ?: 'No se pudo actualizar el producto (según SP).')
                    ->danger()
                    ->send();
                // Considera $this->halt(); si el error del SP es crítico.
            }
        } catch (\Exception $e) {
            report($e);
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al actualizar el producto: ' . $e->getMessage())
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