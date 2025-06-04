<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Stock; // Modelo principal
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EditStock extends EditRecord
{
    protected static string $resource = StockResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // $record es la instancia de Stock que se está editando.
        $stockId = $record->getKey();

        Log::info('EditStock - handleRecordUpdate - Inicio', ['stock_id' => $stockId, 'data_recibida' => $data]);

        // Los datos vienen del formulario. 'producto_id' no debería estar en $data si se configuró ->dehydrated()
        // correctamente en el formulario para la operación de edición.
        $cantidadDisponible = $data['cantidad_disponible'] ?? $record->cantidad_disponible; // Usa valor actual si no viene
        $loteNumero = $data['lote_numero'] ?? null;
        $fechaCaducidad = $data['fecha_caducidad'] ?? null;
        $ubicacionAlmacen = $data['ubicacion_almacen'] ?? null;
        $dbEditorConnection = DB::connection('mysql_editor');
        try {
            $dbEditorConnection->statement(
                "CALL sp_actualizar_stock(?, ?, ?, ?, ?, @success, @message)",
                [
                    $stockId,
                    $cantidadDisponible,
                    $loteNumero,
                    $fechaCaducidad,
                    $ubicacionAlmacen
                ]
            );

            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                Notification::make()
                    ->title('¡Éxito!')
                    ->body($result->message ?: 'Entrada de stock actualizada exitosamente.')
                    ->success()
                    ->send();
            } else {
                $errorMessage = 'Error desconocido desde el SP.';
                if ($result && isset($result->message)) {
                    $errorMessage = $result->message;
                } elseif (!$result) {
                    $errorMessage = 'El SP no devolvió un resultado (variables @success, @message no recuperadas).';
                }
                
                Notification::make()->title('Error al Actualizar Stock')
                    ->body($errorMessage)
                    ->danger()->send();
                // $this->halt(); // Opcional: detener el flujo si es un error crítico
            }
        } catch (\Exception $e) {
            
            report($e);
            Notification::make()->title('Error Inesperado de Base de Datos')
                ->body('Ocurrió un problema técnico al actualizar la entrada de stock: ' . $e->getMessage())
                ->danger()->send();
            // $this->halt(); // Opcional: detener el flujo
        }

        return $record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Desactiva la notificación de "Guardado" por defecto de Filament
    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

}