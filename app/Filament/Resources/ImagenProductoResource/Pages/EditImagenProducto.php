<?php

namespace App\Filament\Resources\ImagenProductoResource\Pages;

use App\Filament\Resources\ImagenProductoResource;
use App\Models\ImagenProducto; // Sigue siendo útil para el casting dentro del método si es necesario
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage; // Para manejar archivos antiguos
use Illuminate\Database\Eloquent\Model; // Importar la clase Model

class EditImagenProducto extends EditRecord
{
    protected static string $resource = ImagenProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (Model $record) { // Cambiado a Model, pero puedes castear dentro si es seguro
                    // Eliminar archivo físico después de borrar el registro
                    if ($record instanceof ImagenProducto && $record->url_imagen) {
                        Storage::disk('public')->delete($record->url_imagen);
                    }
                }),
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Imagen de producto actualizada';
    }

    // Opcional: Manejar la eliminación del archivo antiguo si se sube uno nuevo
    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     $currentRecord = $this->getRecord();
    //     // Si se sube una nueva imagen y la imagen antigua existe y es diferente
    //     if ($currentRecord instanceof ImagenProducto && isset($data['url_imagen']) && $currentRecord->url_imagen && $data['url_imagen'] !== $currentRecord->url_imagen) {
    //         Storage::disk('public')->delete($currentRecord->url_imagen);
    //     }
    //     return $data;
    // }

    // Alternativa más robusta para manejar el archivo antiguo al actualizar
    // La firma del método debe coincidir con la clase padre EditRecord
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Asegurarse de que estamos tratando con una instancia de ImagenProducto
        if (!$record instanceof ImagenProducto) {
            // Puedes lanzar una excepción o manejar el error como prefieras
            // Por ahora, simplemente llamamos al método padre si no es el tipo esperado.
            return parent::handleRecordUpdate($record, $data);
        }

        $oldImagePath = $record->url_imagen;

        $record->fill($data);

        // Si la imagen ha cambiado y la antigua existía
        if ($record->isDirty('url_imagen') && $oldImagePath) {
            // Y la nueva imagen no es nula (es decir, se subió una nueva o se limpió el campo)
            if ($record->url_imagen) { // Se subió una nueva
                 Storage::disk('public')->delete($oldImagePath);
            } else { // El campo se limpió (se eliminó la imagen sin reemplazarla)
                 Storage::disk('public')->delete($oldImagePath);
            }
        }
        $record->save();
        return $record;
    }
}
