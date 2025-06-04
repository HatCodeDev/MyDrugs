<?php

namespace App\Filament\Resources\RepartidorResource\Pages;

use App\Filament\Resources\RepartidorResource;
use App\Models\Repartidor; // Modelo Eloquent base
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
// use Illuminate\Support\Facades\Log; // Para debugging si es necesario

class CreateRepartidor extends CreateRecord
{
    protected static string $resource = RepartidorResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Obtener los datos del formulario
        $userId = $data['user_id'] ?? null; // Puede ser null
        $nombreAlias = $data['nombre_alias'];
        $vehiculoDescripcion = $data['vehiculo_descripcion'] ?? null;
        $zonaOperativaPreferida = $data['zona_operativa_preferida'] ?? null;
        $disponible = $data['disponible'] ?? true; // Default true si no se envía
        $calificacionPromedio = $data['calificacion_promedio'] ?? null;
        $numeroContactoCifrado = $data['numero_contacto_cifrado'] ?? null;

        // Obtener la conexión específica para el 'inserter'
        $dbInserterConnection = DB::connection('mysql_inserter');
        try {
            $dbInserterConnection->statement(
                "CALL sp_crear_repartidor(?, ?, ?, ?, ?, ?, ?, @success, @message, @repartidor_id)",
                [
                    $userId,
                    $nombreAlias,
                    $vehiculoDescripcion,
                    $zonaOperativaPreferida,
                    $disponible,
                    $calificacionPromedio,
                    $numeroContactoCifrado
                ]
            );

            $result = $dbInserterConnection->selectOne("SELECT @success AS success, @message AS message, @repartidor_id AS repartidor_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Repartidor creado exitosamente!')
                    ->success()
                    ->send();

                $repartidor = Repartidor::find($result->repartidor_id);

                if (!$repartidor) {
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('El repartidor se creó en BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()
                        ->send();
                    $this->halt();
                    return new Repartidor();
                }
                return $repartidor;
            } else {
                Notification::make()
                    ->title('Error al crear repartidor')
                    ->body($result->message ?: 'No se pudo guardar el repartidor (según SP).')
                    ->danger()
                    ->send();
                $this->halt();
                return new Repartidor();
            }
        } catch (\Exception $e) {
            report($e);
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al guardar el repartidor: ' . $e->getMessage())
                ->danger()
                ->send();
            $this->halt();
            return new Repartidor();
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