<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use App\Models\Producto; // Modelo Eloquent base
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
// use Illuminate\Support\Facades\Log; // Para debugging si es necesario

class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Obtener los datos del formulario
        $categoriaId = $data['categoria_id'];
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'];
        $precioUnitario = $data['precio_unitario'];
        $unidadMedida = $data['unidad_medida'];
        $activo = $data['activo'] ?? true; // Default true si no se envía
        $dbInserterConnection = DB::connection('mysql_inserter');
        try {
            $dbInserterConnection->statement( 
                "CALL sp_crear_producto(?, ?, ?, ?, ?, ?, @success, @message, @producto_id)",
                [
                    $categoriaId,
                    $nombre,
                    $descripcion,
                    $precioUnitario,
                    $unidadMedida,
                    $activo
                ]
            );

            $result = $dbInserterConnection->selectOne("SELECT @success AS success, @message AS message, @producto_id AS producto_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Producto creado exitosamente!')
                    ->success()
                    ->send();

                $producto = Producto::find($result->producto_id);

                if (!$producto) {
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('El producto se creó en BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()
                        ->send();
                    $this->halt();
                    return new Producto();
                }
                return $producto;
            } else {
                Notification::make()
                    ->title('Error al crear producto')
                    ->body($result->message ?: 'No se pudo guardar el producto (según SP).')
                    ->danger()
                    ->send();
                $this->halt();
                return new Producto();
            }
        } catch (\Exception $e) {
            report($e);
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al guardar el producto: ' . $e->getMessage())
                ->danger()
                ->send();
            $this->halt();
            return new Producto();
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