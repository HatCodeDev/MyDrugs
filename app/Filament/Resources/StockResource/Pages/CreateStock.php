<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Stock; 
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Facades\DB;     
use Filament\Notifications\Notification;

class CreateStock extends CreateRecord
{
    protected static string $resource = StockResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $productoId = $data['producto_id'];
        $cantidadDisponible = $data['cantidad_disponible'] ?? 0; 
        $loteNumero = $data['lote_numero'] ?? null;
        $fechaCaducidad = $data['fecha_caducidad'] ?? null;
        $ubicacionAlmacen = $data['ubicacion_almacen'] ?? null;

        // Obtener la conexión específica para el 'inserter'
        $dbInserterConnection = DB::connection('mysql_inserter');

        try {
            $dbInserterConnection->statement(
                "CALL sp_crear_stock(?, ?, ?, ?, ?, @success, @message, @stock_id)",
                [
                    $productoId,
                    $cantidadDisponible,
                    $loteNumero,
                    $fechaCaducidad,
                    $ubicacionAlmacen
                ]
            );

            $result = $dbInserterConnection->selectOne("SELECT @success AS success, @message AS message, @stock_id AS stock_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title('¡Éxito!')
                    ->body($result->message ?: 'Entrada de stock creada exitosamente.')
                    ->success()
                    ->send();

                $stockEntry = Stock::find($result->stock_id);
                if (!$stockEntry) {
                    Notification::make()->title('Error de Sincronización')
                        ->body('La entrada de stock se creó en la BD pero no se pudo cargar el modelo.')
                        ->danger()->send();
                    $this->halt(); 
                    return new Stock();
                }
                return $stockEntry;
            } else {
                $errorMessage = 'Error desconocido desde el SP.';
                if ($result && isset($result->message)) {
                    $errorMessage = $result->message;
                } elseif (!$result) {
                    $errorMessage = 'El SP no devolvió un resultado (variables @success, @message no recuperadas).';
                }
                Notification::make()->title('Error al Crear Stock')
                    ->body($errorMessage)
                    ->danger()->send();
                $this->halt(); 
                return new Stock();
            }
        } catch (\Exception $e) {
            report($e);
            Notification::make()->title('Error Inesperado de Base de Datos')
                ->body('Ocurrió un problema técnico al crear la entrada de stock: ' . $e->getMessage())
                ->danger()->send();
            $this->halt(); 
            return new Stock(); 
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Desactivamos la notificación por defecto de Filament ya que la manejamos manualmente
    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }
}