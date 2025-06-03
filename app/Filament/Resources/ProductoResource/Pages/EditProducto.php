<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model; // Importar la clase Model
use Illuminate\Support\Facades\DB; // Importar la fachada DB
use Filament\Notifications\Notification; // Importar la clase Notification

class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;

    /**
     * Define las acciones del encabezado de la página de edición.
     * Aquí, la acción de eliminar se mantiene por defecto, pero podrías
     * personalizarla para llamar a un SP de eliminación si lo deseas.
     *
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            // Si quieres que el DeleteAction use un SP, tendrías que sobrescribir su `action()`:
            // Actions\DeleteAction::make()
            //     ->action(function (Model $record) {
            //         // Aquí iría la lógica para llamar a sp_eliminar_producto
            //         // similar a handleRecordUpdate, con try-catch y notificaciones.
            //         // Después de la eliminación exitosa, podrías redirigir:
            //         // return redirect()->to(static::getResource()::getUrl('index'));
            //     }),
        ];
    }

    /**
     * Sobrescribe el método de actualización de registro para llamar a un Stored Procedure.
     *
     * @param \Illuminate\Database\Eloquent\Model $record La instancia del modelo del registro actual.
     * @param array $data Los datos actualizados del formulario.
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Obtener el ID del producto del registro actual
        $productoId = $record->getKey();

        // Extraer los datos del formulario. Asegúrate de que los nombres de las claves
        // coincidan con los nombres de los campos en tu formulario Filament.
        $categoriaId = $data['categoria_id'];
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'];
        $precioUnitario = $data['precio_unitario'];
        $unidadMedida = $data['unidad_medida'];
        $activo = $data['activo'] ?? false; // Para actualización, asume 'false' si no se marca

        try {
            // Llama al Stored Procedure sp_actualizar_producto
            // Asegúrate de que el orden de los parámetros coincida con la definición de tu SP.
            DB::statement(
                "CALL sp_actualizar_producto(?, ?, ?, ?, ?, ?, ?, @success, @message)",
                [
                    $productoId,
                    $categoriaId,
                    $nombre,
                    $descripcion,
                    $precioUnitario,
                    $unidadMedida,
                    $activo
                ]
            );

            // Obtiene los valores de las variables de salida del SP
            $result = DB::selectOne("SELECT @success AS success, @message AS message");

            if ($result && $result->success) {
                // Muestra una notificación de éxito de Filament
                Notification::make()
                    ->title($result->message ?: 'Producto actualizado exitosamente.')
                    ->success()
                    ->send();
            } else {
                // Muestra una notificación de error de Filament
                Notification::make()
                    ->title('Error al actualizar producto')
                    ->body($result->message ?: 'No se pudo actualizar el producto a través del Stored Procedure.')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            // Captura cualquier excepción inesperada (ej. error de conexión a BD)
            report($e); // Registra la excepción en los logs de Laravel
            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al intentar actualizar el producto: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        // Importante: Refresca el modelo para que los cambios hechos por el SP
        // se reflejen en la interfaz de Filament.
        return $record->refresh();
    }

    /**
     * Sobrescribe este método para desactivar la notificación de guardado por defecto de Filament.
     *
     * @return string|null
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return null; // Devolver null desactiva la notificación por defecto
    }

    /**
     * Define la URL de redirección después de una actualización exitosa.
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        // Puedes redirigir a la página de índice o a la misma página de edición
        return $this->getResource()::getUrl('index');
    }
}
