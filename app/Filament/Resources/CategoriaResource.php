<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaResource\Pages;
use App\Models\Categoria;         // Modelo Eloquent base para el recurso 'categorias'
use App\Models\VCategoriasDetalle; // Modelo Eloquent para tu vista 'v_categorias_detalle'
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
// Si necesitas Collection para BulkActions personalizadas:
// use Illuminate\Database\Eloquent\Collection;
// Si necesitas Get para validaciones complejas en el form:
// use Filament\Forms\Get;
// Si necesitas Auth para el form (no parece ser el caso para categorías directamente):
// use Illuminate\Support\Facades\Auth;


class CategoriaResource extends Resource
{
    // Modelo Eloquent base para este recurso
    protected static ?string $model = Categoria::class;

    // Configuración de la navegación
    protected static ?string $navigationIcon = 'heroicon-o-tag'; // Ícono para categorías
    protected static ?string $modelLabel = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';

    // Atributo del modelo que se usará para los títulos de los registros (ej. en la pág. de edición)
    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->label('Nombre de la Categoría'),
                    // Puedes añadir una validación 'unique' aquí para mejorar la UX en el frontend,
                    // aunque tu SP ya maneja la unicidad en el backend.
                    // ->unique(ignoreRecord: true), // ignorará el registro actual al editar
                Forms\Components\Textarea::make('descripcion')
                    ->nullable()
                    ->columnSpanFull() // Ocupa todo el ancho si el form usa columnas
                    ->label('Descripción'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Usa el modelo de la VISTA como fuente de datos principal para la tabla
            ->query(VCategoriasDetalle::query())
            ->columns([
                // Columnas de tu VISTA 'v_categorias_detalle'
                Tables\Columns\TextColumn::make('categoria_id') // El alias de 'id' en tu vista
                    ->label('ID Cat.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50) // Limita el texto mostrado en la celda
                    ->tooltip('Ver descripción completa') // Muestra completo al pasar el mouse
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ocultable por el usuario

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Aquí puedes definir filtros para tu tabla si los necesitas
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Acción estándar, usa EditCategoria.php
                Tables\Actions\DeleteAction::make()
                    // Acción de eliminación personalizada para llamar al SP
                    ->action(function (Model $record) { // $record es una instancia de VCategoriasDetalle
                        $dbEditorConnection = DB::connection('mysql_editor');
                        try {
                            $dbEditorConnection->statement(
                                "CALL sp_eliminar_categoria(?, @success, @message)",
                                [$record->categoria_id] // Usa el 'categoria_id' de la vista
                            );
                            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

                            if ($result && $result->success) {
                                Notification::make()
                                    ->title($result->message ?: '¡Categoría eliminada exitosamente!')
                                    ->success()->send();
                            } else {
                                Notification::make()
                                    ->title('Error al eliminar')
                                    ->body($result->message ?: 'No se pudo eliminar la categoría.')
                                    ->danger()->send();
                            }
                        } catch (\Exception $e) {
                            report($e); // Para logging del error
                            Notification::make()
                                ->title('Error inesperado en la eliminación')
                                ->body('Ocurrió un problema técnico: ' . $e->getMessage())
                                ->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // NOTA: DeleteBulkAction por defecto usa Eloquent.
                    // Para usar SPs en eliminación masiva, necesitarías una BulkAction personalizada.
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Aquí puedes definir RelationManagers si los necesitas. Por ejemplo:
            // \App\Filament\Resources\CategoriaResource\RelationManagers\ProductosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        // Rutas para las páginas del recurso
        return [
            'index' => Pages\ListCategorias::route('/'),
            'create' => Pages\CreateCategoria::route('/create'),
            'edit' => Pages\EditCategoria::route('/{record}/edit'),
        ];
    }
}