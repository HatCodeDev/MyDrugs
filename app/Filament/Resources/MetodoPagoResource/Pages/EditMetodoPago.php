<?php

namespace App\Filament\Resources\MetodoPagoResource\Pages;

use App\Filament\Resources\MetodoPagoResource;
use App\Models\MetodoPago; // Modelo Eloquent base
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Para verificar si es una URL externa

class EditMetodoPago extends EditRecord
{
    protected static string $resource = MetodoPagoResource::class;

    protected ?string $originalLogoUrl = null;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // Podrías personalizar la DeleteAction aquí también
        ];
    }

    protected function beforeFill(): void
    {
        if ($this->record instanceof MetodoPago) {
            $this->originalLogoUrl = $this->record->logo_url;
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $metodoPagoId = $record->getKey();
        $nombreMetodo = $data['nombre_metodo'];
        $descripcionInstrucciones = $data['descripcion_instrucciones'] ?? null;
        $comisionPorcentaje = $data['comision_asociada_porcentaje'] ?? 0.00;
        $activo = $data['activo'] ?? true;
        
        // $data['logo_url'] será:
        // - Nueva ruta si se subió nuevo logo.
        // - Ruta original si el campo FileUpload no se tocó y estaba prellenado.
        // - null si se "limpió" el campo FileUpload (y el componente lo permite).
        $nuevoLogoUrl = $data['logo_url']; // Puede ser null si no se subió/se limpió
        $logoUrlParaSp = $nuevoLogoUrl;

        // Determinar la URL final para el SP y si hay que borrar un archivo antiguo
        $oldFilePathToDelete = null;

        if (is_null($nuevoLogoUrl) && !is_null($this->originalLogoUrl)) {
            // No se subió un nuevo logo Y el campo se limpió (o FileUpload devuelve null)
            // Y había un logo original. Decidimos si queremos borrar el original o mantenerlo.
            // Si queremos que limpiar el campo signifique borrar el logo:
            // $logoUrlParaSp = null; // Se guardará NULL en la BD
            // $oldFilePathToDelete = $this->originalLogoUrl; // Marcar el original para borrar
            // O si queremos que limpiar el campo NO haga nada y se mantenga el original (si FileUpload no lo sobreescribe con null):
            $logoUrlParaSp = $this->originalLogoUrl; // Se mantiene el original
        } elseif (!is_null($nuevoLogoUrl) && $nuevoLogoUrl !== $this->originalLogoUrl) {
            // Se subió un nuevo logo y es diferente al original (o no había original)
            $logoUrlParaSp = $nuevoLogoUrl; // Usar el nuevo
            if (!is_null($this->originalLogoUrl) && !Str::startsWith($this->originalLogoUrl, ['http://', 'https://'])) {
                $oldFilePathToDelete = $this->originalLogoUrl; // Marcar el original para borrar (si no es URL externa)
            }
        } elseif (is_null($nuevoLogoUrl) && is_null($this->originalLogoUrl)) {
            // No había logo original y no se subió nuevo.
            $logoUrlParaSp = null;
        } else { // $nuevoLogoUrl es igual a $this->originalLogoUrl (no hubo cambios en el campo de subida)
             $logoUrlParaSp = $this->originalLogoUrl;
        }


        try {
            DB::statement(
                "CALL sp_actualizar_metodo_pago(?, ?, ?, ?, ?, ?, @success, @message)",
                [
                    $metodoPagoId,
                    $nombreMetodo,
                    $descripcionInstrucciones,
                    $comisionPorcentaje,
                    $activo,
                    $logoUrlParaSp // La URL del logo que se guardará en la BD
                ]
            );

            $result = DB::selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Método de pago actualizado exitosamente!')
                    ->success()
                    ->send();

                // Si la BD se actualizó y teníamos un archivo viejo que borrar:
                if ($oldFilePathToDelete) {
                    try {
                        if (Storage::disk('public')->exists($oldFilePathToDelete)) {
                            Storage::disk('public')->delete($oldFilePathToDelete);
                            Log::info("METODO_PAGO_UPDATE: Logo antiguo {$oldFilePathToDelete} eliminado tras actualizar metodo_pago_id {$metodoPagoId}.");
                        }
                    } catch (\Exception $fileEx) {
                        Log::error("METODO_PAGO_UPDATE: Excepción al eliminar logo antiguo {$oldFilePathToDelete} para metodo_pago_id {$metodoPagoId}: " . $fileEx->getMessage());
                    }
                }
            } else {
                // El SP indicó un fallo. Si se subió un logo nuevo, deberíamos borrarlo.
                if (!is_null($nuevoLogoUrl) && $nuevoLogoUrl !== $this->originalLogoUrl && !Str::startsWith($nuevoLogoUrl, ['http://', 'https://'])) {
                    Storage::disk('public')->delete($nuevoLogoUrl);
                    Log::warning("METODO_PAGO_UPDATE: Nuevo logo {$nuevoLogoUrl} eliminado porque el SP falló para metodo_pago_id {$metodoPagoId}.");
                }
                Notification::make()
                    ->title('Error al actualizar método de pago')
                    ->body($result->message ?: 'No se pudo actualizar el método de pago (según SP).')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            report($e);
            // Si hubo una excepción y se subió un logo nuevo, borrarlo.
            if (!is_null($nuevoLogoUrl) && $nuevoLogoUrl !== $this->originalLogoUrl && !Str::startsWith($nuevoLogoUrl, ['http://', 'https://'])) {
                 Storage::disk('public')->delete($nuevoLogoUrl);
                 Log::warning("METODO_PAGO_UPDATE: Nuevo logo {$nuevoLogoUrl} eliminado por excepción en SP para metodo_pago_id {$metodoPagoId}.");
            }
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al actualizar el método de pago: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}