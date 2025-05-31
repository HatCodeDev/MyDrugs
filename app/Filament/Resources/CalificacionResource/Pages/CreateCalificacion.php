<?php

namespace App\Filament\Resources\CalificacionResource\Pages;

use App\Filament\Resources\CalificacionResource;
use App\Models\Calificacion; // Asegúrate de importar tu modelo Calificacion
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth; // Para obtener el usuario autenticado si es necesario
use Illuminate\Support\Facades\DB;    // Para ejecutar consultas raw y llamar al SP
use Filament\Notifications\Notification; // Para mostrar notificaciones al usuario

class CreateCalificacion extends CreateRecord
{
    protected static string $resource = CalificacionResource::class;

    /**
     * Sobreescribe el método que maneja la creación del registro.
     *
     * @param array $data Los datos del formulario.
     * @return Model La instancia del modelo creado.
     */
    protected function handleRecordCreation(array $data): Model
    {
        // El user_id ya debería estar en $data gracias al Hidden::make('user_id')->default(Auth::id())
        // en tu CalificacionResource::form()
        $userId = $data['user_id'];
        $productoId = $data['producto_id'];
        $puntuacion = $data['puntuacion'];
        $comentario = $data['comentario'] ?? null; // Asegurar que sea null si no se provee

        try {
            // Llamada al procedimiento almacenado
            // DB::statement no devuelve los parámetros OUT directamente.
            // Primero ejecutamos el CALL, luego seleccionamos los parámetros OUT.
            DB::statement(
                "CALL sp_crear_calificacion(?, ?, ?, ?, @success, @message, @calificacion_id)",
                [
                    $userId,
                    $productoId,
                    $puntuacion,
                    $comentario
                ]
            );

            // Recuperar los parámetros OUT
            $result = DB::selectOne("SELECT @success AS success, @message AS message, @calificacion_id AS calificacion_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Éxito!') // Mensaje del SP o uno genérico
                    ->success()
                    ->send();

                // Filament espera que este método devuelva la instancia del modelo creado.
                // Buscamos el modelo usando el ID devuelto por el SP.
                $calificacion = Calificacion::find($result->calificacion_id);

                if (!$calificacion) {
                    // Esto sería inesperado si el SP indica éxito y devuelve un ID.
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('La calificación se creó en la BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()
                        ->send();
                    // Detener el flujo normal de Filament
                    $this->halt();
                    // Devolver un modelo vacío para cumplir la firma, aunque la acción se detuvo
                    return new Calificacion();
                }
                return $calificacion;

            } else {
                // El SP indicó un fallo (ej. duplicado)
                Notification::make()
                    ->title('Error al crear calificación')
                    ->body($result->message ?: 'No se pudo guardar la calificación (según SP).')
                    ->danger()
                    ->send();

                // Detener el flujo normal de Filament para que no intente guardar por Eloquent.
                $this->halt();
                // Devolver un modelo vacío para cumplir la firma, aunque la acción se detuvo
                return new Calificacion();
            }
        } catch (\Exception $e) {
            // Capturar cualquier otra excepción durante la llamada al SP o manejo de resultados
            report($e); // Reporta la excepción para logging

            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al intentar guardar la calificación: ' . $e->getMessage())
                ->danger()
                ->send();

            $this->halt();
            return new Calificacion(); // Devolver un modelo vacío
        }
    }

    /**
     * Opcional: Define a dónde redirigir después de una creación exitosa.
     * Por defecto, redirige a la página de vista del recurso si existe, o al índice.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }
    /**
     * Opcional: Cambia el mensaje de notificación de Filament por defecto después de crear.
     * Como ya estamos enviando notificaciones personalizadas en handleRecordCreation,
     * podríamos querer deshabilitar la notificación por defecto de Filament.
     * Para ello, puedes retornar null o una cadena vacía.
     */
    // protected function getCreatedNotificationTitle(): ?string
    // {
    //     return null; // Deshabilita la notificación de éxito por defecto de Filament
    // }

    // Si quieres deshabilitar todas las notificaciones por defecto de CreateRecord:
    // protected function getCreatedNotification(): ?Notification
    // {
    // return null;
    // }
}