<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use App\Models\VProductosDetalle; // Modelo para la vista
use App\Models\Categoria; // Para el selector de categorías
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder; // Para búsquedas y ordenamiento en relaciones

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    // Usar un atributo que exista en el modelo base 'Producto'
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nombre') // Relación con el modelo Categoria
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Categoría'),
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre del Producto'),
                Forms\Components\Textarea::make('descripcion')
                    ->required()
                    ->columnSpanFull()
                    ->label('Descripción'),
                Forms\Components\TextInput::make('precio_unitario')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0.01)
                    ->step(0.01)
                    ->label('Precio Unitario'),
                Forms\Components\TextInput::make('unidad_medida')
                    ->required()
                    ->maxLength(50)
                    ->label('Unidad de Medida')
                    ->helperText('Ej: gramo, unidad, mililitro, blister'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Activo en Tienda'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VProductosDetalle::query()) // Usar la vista para el listado
            ->columns([
                Tables\Columns\TextColumn::make('producto_id')
                    ->label('ID Prod.')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_producto') // De la vista
                    ->label('Nombre Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn (VProductosDetalle $record): string => $record->nombre_producto),
                Tables\Columns\TextColumn::make('nombre_categoria') // De la vista
                    ->label('Categoría')
                    ->searchable() // La búsqueda en campos de vistas es directa
                    ->sortable(),   // El ordenamiento en campos de vistas es directo
                Tables\Columns\TextColumn::make('precio_unitario')
                    ->label('Precio')
                    ->money('MXN') // Ajusta la moneda si es necesario
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria_id')
        ->label('Categoría')
        // En lugar de ->relationship('categoria', 'nombre')
        ->options(
            fn () => Categoria::query()->pluck('nombre', 'id')->all()
        )
        ->searchable() // La búsqueda en el dropdown funcionará sobre las opciones cargadas
        ->preload(), // Precarga las opciones
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado Activo')
                    // Este filtro operará sobre la columna 'activo' de la vista
                    ,
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Model $record) { // $record es VProductosDetalle
                      $dbEditorConnection = DB::connection('mysql_editor');  
                      try {
                             $dbEditorConnection->statement(
                                "CALL sp_eliminar_producto(?, @success, @message)",
                                [$record->producto_id] // ID del producto desde la vista
                            );
                            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

                            if ($result && $result->success) {
                                Notification::make()
                                    ->title($result->message ?: '¡Producto eliminado exitosamente!')
                                    ->success()->send();
                            } else {
                                Notification::make()
                                    ->title('Error al eliminar producto')
                                    ->body($result->message ?: 'No se pudo eliminar el producto.')
                                    ->danger()->send();
                            }
                        } catch (\Exception $e) {
                            report($e);
                            Notification::make()
                                ->title('Error inesperado')
                                ->body('Ocurrió un problema técnico: ' . $e->getMessage())
                                ->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Ejemplo:
            // \App\Filament\Resources\ProductoResource\RelationManagers\ImagenesProductoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}