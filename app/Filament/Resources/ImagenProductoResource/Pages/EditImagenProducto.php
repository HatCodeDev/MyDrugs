<?php

namespace App\Filament\Resources\ImagenProductoResource\Pages;

use App\Filament\Resources\ImagenProductoResource;
use App\Models\ImagenProducto; // Modelo Eloquent base
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;     // Para logging

class EditImagenProducto extends EditRecord
{
    protected static string $resource = ImagenProductoResource::class;

    // Almacenará la ruta del archivo original antes de cualquier cambio
    protected ?string $originalImageUrl = null;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make()->// ... si quieres personalizar la acción de eliminar aquí también
        ];
    }

    /**
     * Guarda la URL de la imagen original antes de que los datos del formulario se hidraten.
     */
    protected function beforeFill(): void 
    {
        if ($this->record instanceof ImagenProducto) {
            $this->originalImageUrl = $this->record->url_imagen;
        }
    }

    // Alternativamente, para capturar la URL original, si beforeFill no es suficiente
    // o si prefieres hacerlo antes de guardar:
    // protected function beforeSave(): void
    // {
    // if ($this->record instanceof ImagenProducto) {
    // $this->originalImageUrl = $this->record->getOriginal('url_imagen');
    // }
    // }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $imagenProductoId = $record->getKey();
        $productoId = $data['producto_id'];
        $altText = $data['alt_text'] ?? null;
        $orden = $data['orden'] ?? 0;
        $dbEditorConnection = DB::connection('mysql_editor');

        // $data['url_imagen'] contendrá:
        // - La nueva ruta del archivo si se subió una nueva imagen.
        // - La ruta original si no se tocó el campo de subida y estaba prellenado.
        // - null si el campo se "limpió" (si el componente lo permite y se hizo).
        $nuevaUrlImagen = $data['url_imagen'];
        $urlParaSp = $nuevaUrlImagen; // Por defecto, usamos la nueva (o la que venía en $data)

        // Si no se proporcionó una nueva URL (ej. no se subió un nuevo archivo y el campo no se limpió,
        // o si el componente FileUpload devuelve la URL original cuando no hay cambios),
        // queremos mantener la URL original del registro.
        // Es crucial cómo FileUpload maneja $data['url_imagen'] cuando no se sube un nuevo archivo.
        // Si $data['url_imagen'] es null cuando no se sube nada Y queremos mantener la original:
        if (is_null($nuevaUrlImagen) && !is_null($this->originalImageUrl)) {
            $urlParaSp = $this->originalImageUrl;
        }
        // Si $data['url_imagen'] ya es la original cuando no se sube nada, $urlParaSp ya es correcto.

        $oldFilePathToDelete = null;
        if (!is_null($nuevaUrlImagen) && $nuevaUrlImagen !== $this->originalImageUrl && !is_null($this->originalImageUrl)) {
            // Se subió una nueva imagen y es diferente a la original (y había una original)
            $oldFilePathToDelete = $this->originalImageUrl;
        }

        try {
            $dbEditorConnection->statement(
                "CALL sp_actualizar_imagen_producto(?, ?, ?, ?, ?, @success, @message)",
                [
                    $imagenProductoId,
                    $productoId,
                    $urlParaSp, // La URL que irá a la BD
                    $altText,
                    $orden
                ]
            );

            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Imagen de producto actualizada exitosamente!')
                    ->success()
                    ->send();

                // Si la BD se actualizó y teníamos un archivo viejo que reemplazar, lo borramos.
                if ($oldFilePathToDelete) {
                    try {
                        if (Storage::disk('public')->exists($oldFilePathToDelete)) {
                            Storage::disk('public')->delete($oldFilePathToDelete);
                            Log::info("IMAGEN_PRODUCTO_UPDATE: Archivo antiguo {$oldFilePathToDelete} eliminado tras actualizar imagen_id {$imagenProductoId}.");
                        } else {
                             Log::info("IMAGEN_PRODUCTO_UPDATE: Archivo antiguo {$oldFilePathToDelete} no encontrado para eliminar tras actualizar imagen_id {$imagenProductoId}.");
                        }
                    } catch (\Exception $fileEx) {
                        Log::error("IMAGEN_PRODUCTO_UPDATE: Excepción al eliminar archivo antiguo {$oldFilePathToDelete} para imagen_id {$imagenProductoId}: " . $fileEx->getMessage());
                        // Considerar una notificación adicional si esto es crítico
                    }
                }
            } else {
                // El SP indicó un fallo. Si se subió un archivo nuevo, deberíamos borrarlo
                // porque la actualización de la BD falló.
                if (!is_null($nuevaUrlImagen) && $nuevaUrlImagen !== $this->originalImageUrl) {
                    Storage::disk('public')->delete($nuevaUrlImagen);
                     Log::warning("IMAGEN_PRODUCTO_UPDATE: Nuevo archivo {$nuevaUrlImagen} eliminado porque el SP falló para imagen_id {$imagenProductoId}.");
                }
                Notification::make()
                    ->title('Error al actualizar imagen')
                    ->body($result->message ?: 'No se pudo actualizar la imagen (según SP).')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            report($e);
            // Si se subió un archivo nuevo y hubo una excepción, también lo borramos.
            if (!is_null($nuevaUrlImagen) && $nuevaUrlImagen !== $this->originalImageUrl) {
                 Storage::disk('public')->delete($nuevaUrlImagen);
                 Log::warning("IMAGEN_PRODUCTO_UPDATE: Nuevo archivo {$nuevaUrlImagen} eliminado por excepción en SP para imagen_id {$imagenProductoId}.");
            }
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al actualizar la imagen: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return $record->refresh();
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