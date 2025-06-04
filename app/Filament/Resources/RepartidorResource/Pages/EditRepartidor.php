<?php

namespace App\Filament\Resources\RepartidorResource\Pages;

use App\Filament\Resources\RepartidorResource;
// No necesitas 'use App\Models\Repartidor;' si usas el tipado Model de Eloquent
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Actions; // Si quieres añadir acciones como DeleteAction en la cabecera

class EditRepartidor extends EditRecord
{
    protected static string $resource = RepartidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // Puedes añadirla aquí si la personalizas para usar tu SP
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $repartidorId = $record->getKey();
        $userId = $data['user_id'] ?? null;
        $nombreAlias = $data['nombre_alias'];
        $vehiculoDescripcion = $data['vehiculo_descripcion'] ?? null;
        $zonaOperativaPreferida = $data['zona_operativa_preferida'] ?? null;
        $disponible = $data['disponible'] ?? true;
        $calificacionPromedio = $data['calificacion_promedio'] ?? null;
        $numeroContactoCifrado = $data['numero_contacto_cifrado'] ?? null;
        $dbEditorConnection = DB::connection('mysql_editor');
        try {
            $dbEditorConnection->statement(
                "CALL sp_actualizar_repartidor(?, ?, ?, ?, ?, ?, ?, ?, @success, @message)",
                [
                    $repartidorId,
                    $userId,
                    $nombreAlias,
                    $vehiculoDescripcion,
                    $zonaOperativaPreferida,
                    $disponible,
                    $calificacionPromedio,
                    $numeroContactoCifrado
                ]
            );

            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Repartidor actualizado exitosamente!')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Error al actualizar repartidor')
                    ->body($result->message ?: 'No se pudo actualizar el repartidor (según SP).')
                    ->danger()
                    ->send();
                // Considera $this->halt(); si el error del SP es crítico y no quieres que se refresque el modelo.
            }
        } catch (\Exception $e) {
            report($e);
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al actualizar el repartidor: ' . $e->getMessage())
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