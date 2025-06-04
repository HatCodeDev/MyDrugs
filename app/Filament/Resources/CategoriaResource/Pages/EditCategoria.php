<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
// No necesitas 'use App\Models\Categoria;' si usas el tipado Model de Eloquent
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Actions; // Si necesitas añadir acciones en la cabecera

class EditCategoria extends EditRecord
{
    protected static string $resource = CategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Puedes añadir Actions\DeleteAction::make() aquí si quieres un botón de eliminar
            // en la cabecera de la página de edición, pero también estará en la tabla.
            // Si lo haces, asegúrate de personalizarla para usar tu SP de eliminación.
            // Actions\DeleteAction::make()
            // ->action(function (Model $record) { /* ... lógica con sp_eliminar_categoria ... */ }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $categoriaId = $record->getKey(); // Obtiene el ID de la categoría actual
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'] ?? null;
        $dbEditorConnection = DB::connection('mysql_editor');

        try {
            // Llamada al procedimiento almacenado para actualizar una categoría
            // Asumo que tu SP se llama 'sp_actualizar_categoria'
            $dbEditorConnection->statement(
                "CALL sp_actualizar_categoria(?, ?, ?, @success, @message)",
                [
                    $categoriaId,
                    $nombre,
                    $descripcion
                ]
            );

            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Categoría actualizada exitosamente!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al actualizar categoría')
                    ->body($result->message ?: 'No se pudo actualizar la categoría (según SP).')
                    ->danger()
                    ->send();
                // Considera si necesitas $this->halt() aquí si el error del SP es crítico.
            }
        } catch (\Exception $e) {
            report($e); // Reporta la excepción

            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al intentar actualizar la categoría: ' . $e->getMessage())
                ->danger()
                ->send();
            // $this->halt(); // Opcional, si el error es crítico y quieres detener el flujo
        }

        // Importante: Llama a refresh() para asegurar que el modelo en memoria ($record)
        // refleje los cambios hechos directamente en la base de datos por el SP.
        return $record->refresh();
    }

    /**
     * Deshabilita la notificación de "Cambios guardados" por defecto de Filament,
     * ya que estamos enviando una personalizada.
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}