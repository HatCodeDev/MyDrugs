<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
use App\Models\Categoria; // Asegúrate que este sea tu modelo Eloquent para la tabla 'categorias'
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateCategoria extends CreateRecord
{
    protected static string $resource = CategoriaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Obtén los datos del formulario.
        // Para 'categorias', probablemente tengas 'nombre' y 'descripcion'.
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'] ?? null; // Asegurar null si no se provee

        try {
            // Llamada al procedimiento almacenado para crear una categoría
            // Asumo que tu SP se llama 'sp_crear_categoria' y tiene parámetros OUT similares
            DB::statement(
                "CALL sp_crear_categoria(?, ?, @success, @message, @categoria_id)",
                [
                    $nombre,
                    $descripcion
                ]
            );

            // Recuperar los parámetros OUT
            $result = DB::selectOne("SELECT @success AS success, @message AS message, @categoria_id AS categoria_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title($result->message ?: '¡Categoría creada exitosamente!')
                    ->success()
                    ->send();

                // Filament espera que este método devuelva la instancia del modelo creado.
                $categoria = Categoria::find($result->categoria_id);

                if (!$categoria) {
                    // Esto sería inesperado si el SP indica éxito y devuelve un ID.
                    Notification::make()
                        ->title('Error de sincronización')
                        ->body('La categoría se creó en la BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()
                        ->send();
                    $this->halt(); // Detener el flujo normal de Filament
                    return new Categoria(); // Devolver un modelo vacío para cumplir la firma
                }
                return $categoria;

            } else {
                // El SP indicó un fallo (ej. nombre duplicado)
                Notification::make()
                    ->title('Error al crear categoría')
                    ->body($result->message ?: 'No se pudo guardar la categoría (según SP).')
                    ->danger()
                    ->send();
                $this->halt(); // Detener el flujo normal de Filament
                return new Categoria(); // Devolver un modelo vacío
            }
        } catch (\Exception $e) {
            // Capturar cualquier otra excepción
            report($e); // Reporta la excepción para logging

            Notification::make()
                ->title('Error inesperado')
                ->body('Ocurrió un problema técnico al intentar guardar la categoría: ' . $e->getMessage())
                ->danger()
                ->send();

            $this->halt();
            return new Categoria(); // Devolver un modelo vacío
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Deshabilita la notificación de éxito por defecto de Filament,
     * ya que estamos enviando una personalizada desde handleRecordCreation.
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }
}