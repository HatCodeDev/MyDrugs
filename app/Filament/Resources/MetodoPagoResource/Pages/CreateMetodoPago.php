<?php

namespace App\Filament\Resources\MetodoPagoResource\Pages;

use App\Filament\Resources\MetodoPagoResource;
use App\Models\MetodoPago; // Modelo Eloquent base
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CreateMetodoPago extends CreateRecord
{
    protected static string $resource = MetodoPagoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $nombreMetodo = $data['nombre_metodo'];
        $descripcionInstrucciones = $data['descripcion_instrucciones'] ?? null;
        $comisionPorcentaje = $data['comision_asociada_porcentaje'] ?? 0.00;
        $activo = $data['activo'] ?? true;
        // $data['logo_url'] contendrá la ruta relativa si se subió un archivo, o null si no.
        $logoUrl = $data['logo_url'] ?? null;

        // Obtener la conexión específica para el 'inserter'
        $dbInserterConnection = DB::connection('mysql_inserter');

        try {
             $dbInserterConnection->statement(
                "CALL sp_crear_metodo_pago(?, ?, ?, ?, ?, @success, @message, @metodo_pago_id)",
                [
                    $nombreMetodo,
                    $descripcionInstrucciones,
                    $comisionPorcentaje,
                    $activo,
                    $logoUrl // Pasamos la ruta del logo (o null) al SP
                ]
            );

            $result = $dbInserterConnection->selectOne("SELECT @success AS success, @message AS message, @metodo_pago_id AS metodo_pago_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Método de pago creado exitosamente!')
                    ->success()
                    ->send();

                $metodoPago = MetodoPago::find($result->metodo_pago_id);

                if (!$metodoPago) {
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('El método de pago se creó en BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()
                        ->send();
                    // Si el modelo no se cargó pero el SP fue exitoso y se subió un logo,
                    // podría quedar un archivo huérfano. Considerar borrarlo.
                    if ($logoUrl) {
                        Storage::disk('public')->delete($logoUrl);
                        Log::warning("METODO_PAGO_CREATE: Logo {$logoUrl} eliminado por fallo al cargar modelo post-creación SP.");
                    }
                    $this->halt();
                    return new MetodoPago();
                }
                return $metodoPago;
            } else {
                // El SP indicó un fallo. Si se subió un logo, hay que borrarlo.
                if ($logoUrl) {
                    Storage::disk('public')->delete($logoUrl);
                }
                Notification::make()
                    ->title('Error al crear método de pago')
                    ->body($result->message ?: 'No se pudo guardar el método de pago (según SP).')
                    ->danger()
                    ->send();
                $this->halt();
                return new MetodoPago();
            }
        } catch (\Exception $e) {
            report($e);
            // Si hubo una excepción y se había subido un logo, borrarlo.
            if (isset($logoUrl) && $logoUrl) {
                 Storage::disk('public')->delete($logoUrl);
            }
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al guardar el método de pago: ' . $e->getMessage())
                ->danger()
                ->send();
            $this->halt();
            return new MetodoPago();
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