<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaResource\Pages;
use App\Filament\Resources\CategoriaResource\RelationManagers;
use App\Models\Categoria; // Asegúrate que esta ruta al modelo sea correcta
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriaResource extends Resource
{
    protected static ?string $model = Categoria::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag'; // Icono para categorías
    protected static ?string $modelLabel = 'Categoría'; // Etiqueta singular para el modelo
    protected static ?string $pluralModelLabel = 'Categorías'; // Etiqueta plural para el modelo
    protected static ?string $recordTitleAttribute = 'nombre'; // Atributo para mostrar como título del registro


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre de la Categoría')
                    ->required()
                    ->unique(ignoreRecord: true) // Único, ignorando el registro actual al editar
                    ->maxLength(255)
                    ->placeholder('Ej: Vitaminas y Suplementos')
                    ->columnSpanFull(), // Ocupa todo el ancho

                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->nullable()
                    ->maxLength(65535) // Límite para TEXT
                    ->rows(3) // Define un número de filas inicial para el textarea
                    ->placeholder('Describe brevemente la categoría...')
                    ->columnSpanFull(),
            ])
            ->columns(1); // Una columna para el formulario
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50) // Limita el texto en la tabla
                    ->tooltip(fn (Categoria $record): ?string => $record->descripcion) // Muestra completo al pasar el mouse
                    ->toggleable(isToggledHiddenByDefault: false), // Visible por defecto
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i') // Formato de fecha y hora
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Puedes añadir filtros aquí si es necesario
                // Ejemplo: Tables\Filters\TrashedFilter::make(), si usas SoftDeletes
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(), // Para ver detalles sin editar
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Ejemplo: Tables\Actions\ForceDeleteBulkAction::make(),
                    // Ejemplo: Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([ // Acciones cuando la tabla está vacía
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Si una categoría tiene productos, podrías añadir un RelationManager aquí.
            // Ejemplo: RelationManagers\ProductosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategorias::route('/'),
            'create' => Pages\CreateCategoria::route('/create'),
            'edit' => Pages\EditCategoria::route('/{record}/edit'),
            // Podrías añadir una página de vista si la necesitas
            // 'view' => Pages\ViewCategoria::route('/{record}'),
        ];
    }
}
