<?php

namespace App\Filament\Resources\CalificacionResource\Pages;

use App\Filament\Resources\CalificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model; // Necesario para el tipado de $record y el retorno
use Illuminate\Support\Facades\DB;     // Para ejecutar consultas raw y llamar al SP
use Filament\Notifications\Notification; // Para mostrar notificaciones al usuario

class EditCalificacion extends EditRecord
{
    protected static string $resource = CalificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    /**
     * Sobreescribe el método que maneja la actualización del registro.
     *
     * @param Model $record El modelo Eloquent del registro actual.
     * @param array $data Los datos del formulario.
     * @return Model La instancia del modelo actualizado.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $calificacionId = $record->getKey();
        $puntuacion = $data['puntuacion'];
        $comentario = $data['comentario'] ?? null;

        $dbEditorConnection = DB::connection('mysql_editor');

        try {
            $dbEditorConnection->statement(
                "CALL sp_actualizar_calificacion(?, ?, ?, @success, @message)",
                [
                    $calificacionId,
                    $puntuacion,
                    $comentario
                ]
            );

            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Actualización exitosa!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al actualizar')
                    ->body($result->message ?: 'No se pudo actualizar la calificación (según SP).')
                    ->danger()
                    ->send();
                // Considera si necesitas $this->halt() aquí.
                // Si el SP falla porque el registro no existe, Filament ya podría haberlo manejado
                // al cargar el registro. Si es otro tipo de error del SP, halt() podría ser útil.
            }
        } catch (\Exception $e) {
            report($e); // Reporta la excepción

            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al intentar actualizar: ' . $e->getMessage())
                ->danger()
                ->send();
            // $this->halt(); // Opcional, si el error es crítico
        }

       
        return $record->refresh();
    }

    /**
     * Devuelve null para desactivar el título de la notificación de guardado por defecto de Filament.
     * Esto previene que la notificación por defecto "Changes saved" aparezca.
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    // Alternativamente, para desactivar completamente el objeto de notificación:
    // protected function getSavedNotification(): ?Notification
    // {
    //     return null;
    // }

    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}