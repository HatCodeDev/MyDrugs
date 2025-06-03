<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto; // El modelo principal del recurso
use App\Models\VProductoDetalle; // Tu modelo de vista
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // Para el tipo de $record en las acciones
use Illuminate\Support\Facades\DB; // Para llamar a Stored Procedures
use Filament\Notifications\Notification; // Para notificaciones personalizadas

class ProductoResource extends Resource
{
    // El modelo principal del recurso sigue siendo Producto, ya que los formularios
    // de Crear y Editar interactúan directamente con la tabla 'productos'.
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube'; // Un ícono de ejemplo

    // Define el formulario para crear y editar productos.
    // Esto sigue interactuando con el modelo 'Producto' directamente.
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nombre')
                    ->required()
                    ->label('Categoría'),
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre del Producto'),
                Forms\Components\Textarea::make('descripcion')
                    ->nullable()
                    ->rows(3)
                    ->label('Descripción'),
                Forms\Components\TextInput::make('precio_unitario')
                    ->required()
                    ->numeric()
                    ->prefix('MXN')
                    ->label('Precio Unitario'),
                Forms\Components\TextInput::make('unidad_medida')
                    ->required()
                    ->maxLength(50)
                    ->label('Unidad de Medida'),
                Forms\Components\Toggle::make('activo')
                    ->default(true)
                    ->label('Activo'),
            ]);
    }

    // Configura la tabla de listado para usar la vista y personalizar las acciones.
    public static function table(Table $table): Table
    {
        return $table
            // *** Fuente de datos principal: Tu modelo de vista ***
            ->query(VProductoDetalle::query()) // Usa el modelo de la vista como fuente de datos
            ->columns([
                Tables\Columns\TextColumn::make('producto_id')
                    ->label('ID Producto')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('producto_nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('categoria_nombre')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('producto_precio_unitario')
                    ->label('Precio')
                    ->money('MXN') // Formato de moneda
                    ->sortable(),
                Tables\Columns\TextColumn::make('producto_unidad_medida')
                    ->label('Unidad')
                    ->sortable(),
                Tables\Columns\IconColumn::make('producto_activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('producto_created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto
                Tables\Columns\TextColumn::make('producto_updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto
            ])
            ->filters([
                // Filtros basados en las columnas de la vista
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->relationship('categoria', 'nombre') // Asume que VProductoDetalle tiene una relación 'categoria'
                    ->label('Filtrar por Categoría'),
                Tables\Filters\TernaryFilter::make('producto_activo')
                    ->label('Estado Activo')
                    ->boolean(),
                // Puedes añadir más filtros aquí
            ])
            ->actions([
                // Acción de Editar: Filament la gestiona para redirigir a EditProducto.php
                // y usa el modelo principal (Producto) para cargar el formulario.
                Tables\Actions\EditAction::make(),

                // *** Personalización de DeleteAction para llamar a SP ***
                Tables\Actions\DeleteAction::make()
                    ->action(function (Model $record) { // $record aquí es una instancia de VProductoDetalle
                        try {
                            // Llama al Stored Procedure sp_eliminar_producto
                            // El ID a eliminar se obtiene de la clave primaria de la vista: $record->producto_id
                            DB::statement(
                                "CALL sp_eliminar_producto(?, @success, @message)",
                                [$record->producto_id] // Pasa el ID del producto de la vista
                            );

                            // Obtiene los valores de las variables de salida del SP
                            $result = DB::selectOne("SELECT @success AS success, @message AS message");

                            if ($result && $result->success) {
                                // Muestra una notificación de éxito de Filament
                                Notification::make()
                                    ->title($result->message ?: 'Producto eliminado exitosamente.')
                                    ->success()
                                    ->send();
                            } else {
                                // Muestra una notificación de error de Filament
                                Notification::make()
                                    ->title('Error al eliminar producto')
                                    ->body($result->message ?: 'No se pudo eliminar el producto a través del Stored Procedure.')
                                    ->danger()
                                    ->send();
                                // Si hay un error, puedes lanzar una excepción para detener la acción
                                // o simplemente dejar que la notificación informe.
                                // throw new \Exception($result->message ?: 'Error desconocido al eliminar.');
                            }
                        } catch (\Exception $e) {
                            // Captura cualquier excepción inesperada (ej. error de conexión a BD)
                            report($e); // Registra la excepción en los logs de Laravel
                            Notification::make()
                                ->title('Error inesperado')
                                ->body('Ocurrió un problema técnico al intentar eliminar el producto: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                // Filament tiene acciones masivas por defecto. Si quieres que la eliminación masiva
                // también use un SP, tendrías que personalizar Tables\Actions\DeleteBulkAction::make()
                // de manera similar a la acción individual.
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // Define las relaciones del recurso (si las hay).
    // Esto sigue basándose en el modelo principal 'Producto'.
    public static function getRelations(): array
    {
        return [
            // Por ejemplo, si tuvieras un RelationManager para Imágenes del Producto
            // RelationManagers\ImagenesProductoRelationManager::class,
        ];
    }

    // Define las páginas del recurso.
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }

    // Opcional: Si necesitas un scope de soft delete
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
