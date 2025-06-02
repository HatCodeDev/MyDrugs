<?php

namespace App\Filament\Resources\ImagenProductoResource\Pages;

use App\Filament\Resources\ImagenProductoResource;
use App\Models\ImagenProducto; // Modelo Eloquent base
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage; // Para posible limpieza si algo falla después de subir

class CreateImagenProducto extends CreateRecord
{
    protected static string $resource = ImagenProductoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // $data['url_imagen'] ya contendrá la RUTA RELATIVA al disco 'public'
        // donde Filament's FileUpload ha guardado la imagen.
        // Ej: 'imagenes_productos/nombre_archivo.jpg'

        $productoId = $data['producto_id'];
        $urlImagen = $data['url_imagen']; // Esta es la ruta del archivo guardado
        $altText = $data['alt_text'] ?? null;
        $orden = $data['orden'] ?? 0;

        try {
            DB::statement(
                "CALL sp_crear_imagen_producto(?, ?, ?, ?, @success, @message, @imagen_producto_id)",
                [
                    $productoId,
                    $urlImagen, // Pasamos la ruta del archivo al SP
                    $altText,
                    $orden
                ]
            );

            $result = DB::selectOne("SELECT @success AS success, @message AS message, @imagen_producto_id AS imagen_producto_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Imagen de producto creada exitosamente!')
                    ->success()
                    ->send();

                $imagenProducto = ImagenProducto::find($result->imagen_producto_id);

                if (!$imagenProducto) {
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('La imagen se creó en BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()
                        ->send();
                    // Considerar eliminar el archivo físico si la carga del modelo falla,
                    // aunque esto podría ser una condición muy rara.
                    if ($urlImagen) {
                        Storage::disk('public')->delete($urlImagen);
                        Log::warning("IMAGEN_PRODUCTO_CREATE: Archivo físico {$urlImagen} eliminado debido a fallo al cargar modelo post-creación SP.");
                    }
                    $this->halt();
                    return new ImagenProducto();
                }
                return $imagenProducto;
            } else {
                // El SP indicó un fallo. El archivo ya fue subido por Filament. Deberíamos eliminarlo.
                if ($urlImagen) {
                    Storage::disk('public')->delete($urlImagen);
                }
                Notification::make()
                    ->title('Error al crear imagen de producto')
                    ->body($result->message ?: 'No se pudo guardar la imagen (según SP).')
                    ->danger()
                    ->send();
                $this->halt();
                return new ImagenProducto();
            }
        } catch (\Exception $e) {
            report($e);
            // El archivo ya fue subido por Filament. Deberíamos eliminarlo si hay excepción.
            if (isset($urlImagen) && $urlImagen) { // Verificar que $urlImagen esté definida
                 Storage::disk('public')->delete($urlImagen);
            }
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al guardar la imagen: ' . $e->getMessage())
                ->danger()
                ->send();
            $this->halt();
            return new ImagenProducto();
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