<?php

namespace App\Filament\Resources\CalificacionResource\Pages;

use App\Filament\Resources\CalificacionResource;
use App\Models\Calificacion; 
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;    
use Filament\Notifications\Notification; 

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
        $userId = $data['user_id'];
        $productoId = $data['producto_id'];
        $puntuacion = $data['puntuacion'];
        $comentario = $data['comentario'] ?? null; 

        $dbInserterConnection = DB::connection('mysql_inserter'); 

        try {
            $dbInserterConnection->statement( 
                "CALL sp_crear_calificacion(?, ?, ?, ?, @success, @message, @calificacion_id)",
                [
                    $userId,
                    $productoId,
                    $puntuacion,
                    $comentario
                ]
            );

            $result = $dbInserterConnection->selectOne("SELECT @success AS success, @message AS message, @calificacion_id AS calificacion_id"); // <--- Cambio Clave: Usar la conexión específica

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Éxito!') 
                    ->success()
                    ->send();

                $calificacion = Calificacion::find($result->calificacion_id);

                if (!$calificacion) {
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('La calificación se creó en la BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()
                        ->send();
                    $this->halt();
                    return new Calificacion();
                }
                return $calificacion;

            } else {
                Notification::make()
                    ->title('Error al crear calificación')
                    ->body($result->message ?: 'No se pudo guardar la calificación (según SP).')
                    ->danger()
                    ->send();

                $this->halt();
                return new Calificacion();
            }
        } catch (\Exception $e) {
            report($e);

            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al intentar guardar la calificación: ' . $e->getMessage())
                ->danger()
                ->send();

            $this->halt();
            return new Calificacion(); 
        }
    }

    /**
     * Define a dónde redirigir después de una creación exitosa.
     * Por defecto, redirige a la página de vista del recurso si existe, o al índice.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Deshabilita la notificación de éxito por defecto de Filament,
     * ya que estamos enviando una personalizada desde handleRecordCreation.
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }
}