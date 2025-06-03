<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model; // Importar la clase Model
use Illuminate\Support\Facades\DB; // Importar la fachada DB
use Filament\Notifications\Notification; // Importar la clase Notification
use Illuminate\Support\Facades\Auth; // Importar la fachada Auth para user_id

class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;

    /**
     * Sobrescribe el método de creación de registro para llamar a un Stored Procedure.
     *
     * @param array $data Los datos del formulario.
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Extraer los datos del formulario. Asegúrate de que los nombres de las claves
        // coincidan con los nombres de los campos en tu formulario Filament.
        $categoriaId = $data['categoria_id'];
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'];
        $precioUnitario = $data['precio_unitario'];
        $unidadMedida = $data['unidad_medida'];
        $activo = $data['activo'] ?? true; // Asume 'true' si no se proporciona (checkbox)
        $userId = Auth::id(); // Obtiene el ID del usuario autenticado, si es necesario para tu SP

        try {
            // Llama al Stored Procedure sp_crear_producto
            // Asegúrate de que el orden de los parámetros coincida con la definición de tu SP.
            DB::statement(
                "CALL sp_crear_producto(?, ?, ?, ?, ?, ?, ?, @success, @message, @producto_id)",
                [
                    $categoriaId,
                    $nombre,
                    $descripcion,
                    $precioUnitario,
                    $unidadMedida,
                    $activo,
                    $userId // Pasa el user_id si tu SP lo espera
                ]
            );

            // Obtiene los valores de las variables de salida del SP
            $result = DB::selectOne("SELECT @success AS success, @message AS message, @producto_id AS producto_id");

            if ($result && $result->success) {
                // Muestra una notificación de éxito de Filament
                Notification::make()
                    ->title($result->message ?: 'Producto creado exitosamente.')
                    ->success()
                    ->send();

                // Intenta encontrar y devolver el modelo recién creado
                $producto = static::getModel()::find($result->producto_id);

                if (!$producto) {
                    // Si el SP creó el registro pero no se pudo cargar el modelo (sincronización)
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('El producto fue creado en la base de datos, pero no se pudo cargar el modelo en la aplicación.')
                        ->danger()
                        ->send();
                    $this->halt(); // Detiene el proceso de Filament
                    return new (static::getModel())(); // Devuelve una instancia vacía del modelo
                }
                return $producto; // Devuelve el modelo creado
            } else {
                // Muestra una notificación de error de Filament
                Notification::make()
                    ->title('Error al crear producto')
                    ->body($result->message ?: 'No se pudo crear el producto a través del Stored Procedure.')
                    ->danger()
                    ->send();
                $this->halt(); // Detiene el proceso de Filament
                return new (static::getModel())(); // Devuelve una instancia vacía del modelo
            }
        } catch (\Exception $e) {
            // Captura cualquier excepción inesperada (ej. error de conexión a BD)
            report($e); // Registra la excepción en los logs de Laravel
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al intentar crear el producto: ' . $e->getMessage())
                ->danger()
                ->send();
            $this->halt(); // Detiene el proceso de Filament
            return new (static::getModel())(); // Devuelve una instancia vacía del modelo
        }
    }

    /**
     * Sobrescribe este método para desactivar la notificación de éxito por defecto de Filament.
     *
     * @return string|null
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return null; // Devolver null desactiva la notificación por defecto
    }

    /**
     * Define la URL de redirección después de una creación exitosa.
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Redirige a la lista de productos
    }
}
