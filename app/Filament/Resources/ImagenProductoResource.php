<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImagenProductoResource\Pages;
use App\Models\ImagenProducto;         // Modelo Eloquent base para 'imagenes_producto'
use App\Models\VImagenesProductoDetalle; // Modelo Eloquent para tu vista 'v_imagenes_producto_detalle'
// use App\Models\Producto;             // Descomenta si tienes un modelo Producto para el selector
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage; // Para manejar la subida y eliminación de archivos
use Illuminate\Support\Facades\Log;     // Para registrar eventos o errores específicos
// Si necesitas Collection para BulkActions personalizadas:
// use Illuminate\Database\Eloquent\Collection;

class ImagenProductoResource extends Resource
{
    protected static ?string $model = ImagenProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $modelLabel = 'Imagen de Producto';
    protected static ?string $pluralModelLabel = 'Imágenes de Productos';
    protected static ?string $recordTitleAttribute = 'alt_text'; // 'url_imagen' o 'alt_text'

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('producto_id')
                    ->relationship('producto', 'nombre') // Asume relación 'producto' en ImagenProducto y 'nombre' en Producto
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Producto Asociado'),
                Forms\Components\FileUpload::make('url_imagen')
                    ->label('Imagen')
                    ->disk('public') // Asegúrate que 'public' disk esté configurado en filesystems.php
                    ->directory('imagenes_productos') // Directorio dentro del disco 'public'
                    ->image() // Para validación de imagen y previsualización
                    // ->imageEditor() // Opcional: editor de imágenes de Filament
                    ->required(fn (string $context): bool => $context === 'create') // Requerido solo en creación
                    // En edición, si se sube un nuevo archivo, reemplazará al anterior.
                    // Si no se sube, la URL actual se mantendrá (lógica a manejar en EditImagenProducto.php)
                    ->helperText('Sube una nueva imagen para reemplazar la existente al editar, o déjalo vacío para conservarla.'),
                Forms\Components\TextInput::make('alt_text')
                    ->nullable()
                    ->maxLength(255)
                    ->label('Texto Alternativo (Alt Text)'),
                Forms\Components\TextInput::make('orden')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->label('Orden de Visualización'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VImagenesProductoDetalle::query()) // Usa la VISTA para el listado
            ->columns([
                Tables\Columns\TextColumn::make('imagen_producto_id')
                    ->label('ID Imagen')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('url_imagen') // Muestra la imagen directamente
                    ->label('Imagen')
                    ->disk('public') // El mismo disco donde se guardan
                    ->height(60)
                    ->square(), // Opcional: para que la previsualización sea cuadrada
                Tables\Columns\TextColumn::make('producto_nombre') // De la vista (join con productos)
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->limit(30)
                    ->tooltip('Ver texto completo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('orden')
                    ->label('Orden')
                    ->sortable(),
                Tables\Columns\TextColumn::make('imagen_created_at') // De la vista
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Model $record) { // $record es una instancia de VImagenesProductoDetalle
                        $dbSuccess = false;
                        $fileHandledSuccessfully = false; // Para rastrear si el archivo se manejó correctamente
                        $filePath = $record->url_imagen; // Guardar la ruta antes de cualquier cosa
                        $finalMessage = 'Error desconocido durante la eliminación.'; // Mensaje por defecto
                        $dbEditorConnection = DB::connection('mysql_editor');
                        try {
                            // 1. Intentar eliminar el registro de la base de datos
                            $dbEditorConnection->statement(
                                "CALL sp_eliminar_imagen_producto(?, @success, @message)",
                                [$record->imagen_producto_id] // Usar el ID de la imagen desde la vista
                            );
                            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

                            if ($result && $result->success) {
                                $dbSuccess = true;
                                $finalMessage = $result->message ?: 'Registro de imagen eliminado de la base de datos.';

                                // 2. Si la eliminación de la BD fue exitosa, intentar eliminar el archivo físico
                                if (!empty($filePath)) {
                                    try {
                                        if (Storage::disk('public')->exists($filePath)) {
                                            if (Storage::disk('public')->delete($filePath)) {
                                                $fileHandledSuccessfully = true;
                                                // $finalMessage .= ' Archivo físico eliminado.'; // Opcional: añadir al mensaje
                                            } else {
                                                // Falla al eliminar el archivo
                                                Log::warning("IMAGEN_PRODUCTO_DELETE: No se pudo eliminar el archivo físico '{$filePath}' para imagen_id {$record->imagen_producto_id}.");
                                                $finalMessage .= ' Sin embargo, el archivo físico no pudo ser eliminado.';
                                            }
                                        } else {
                                            // El archivo no existía, lo cual está bien en este contexto
                                            Log::info("IMAGEN_PRODUCTO_DELETE: El archivo físico '{$filePath}' no existía para imagen_id {$record->imagen_producto_id}. Se considera manejado.");
                                            $fileHandledSuccessfully = true; // Consideramos que el estado del archivo está "resuelto"
                                        }
                                    } catch (\Exception $fileException) {
                                        Log::error("IMAGEN_PRODUCTO_DELETE: Excepción al eliminar archivo '{$filePath}' para imagen_id {$record->imagen_producto_id}: " . $fileException->getMessage());
                                        $finalMessage .= ' Ocurrió una excepción al intentar eliminar el archivo físico.';
                                    }
                                } else {
                                    // No había ruta de archivo en el registro, así que no hay nada que borrar.
                                    $fileHandledSuccessfully = true; // Consideramos que el estado del archivo está "resuelto"
                                }
                            } else {
                                // El SP indicó un fallo al eliminar de la BD
                                $finalMessage = $result->message ?: 'No se pudo eliminar el registro de la imagen (según SP).';
                            }
                        } catch (\Exception $e) {
                            report($e); // Loguea la excepción de la BD
                            $finalMessage = 'Error inesperado durante la operación de base de datos: ' . $e->getMessage();
                        }

                        // 3. Enviar notificación basada en los resultados
                        if ($dbSuccess && $fileHandledSuccessfully) {
                            Notification::make()
                                ->title('¡Eliminación Completada!')
                                ->body($finalMessage ?: 'La imagen y su archivo asociado han sido eliminados.')
                                ->success()->send();
                        } elseif ($dbSuccess && !$fileHandledSuccessfully) {
                            Notification::make()
                                ->title('Eliminación Parcial')
                                ->body($finalMessage) // El mensaje ya indica el problema con el archivo
                                ->warning()->send();
                        } else { // !$dbSuccess
                            Notification::make()
                                ->title('Error en la Eliminación')
                                ->body($finalMessage)
                                ->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    // NOTA: DeleteBulkAction por defecto usa Eloquent.
                    // Para usar SPs y manejar la eliminación de archivos físicos en masa,
                    // necesitarías una BulkAction personalizada.
                    ,
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImagenProductos::route('/'),
            'create' => Pages\CreateImagenProducto::route('/create'),
            'edit' => Pages\EditImagenProducto::route('/{record}/edit'),
        ];
    }
}