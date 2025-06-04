<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;   
use App\Models\VStockDetalle; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Facades\DB;      
use Filament\Notifications\Notification; 

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box'; 

    protected static ?string $modelLabel = 'Entrada de Stock';
    protected static ?string $pluralModelLabel = 'Gestión de Stock';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('producto_id')
                    ->label('Producto')
                    ->relationship(name: 'producto', titleAttribute: 'nombre') 
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Seleccionar el producto para esta entrada de stock')
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(fn (string $operation): bool => $operation !== 'edit')
                    ->placeholder('Seleccionar el producto para esta entrada de stock')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('cantidad_disponible')
                    ->label('Cantidad Disponible')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->minValue(0)
                    ->placeholder('0'),

                Forms\Components\TextInput::make('lote_numero')
                    ->label('Número de Lote (Opcional)')
                    ->nullable()
                    ->maxLength(100)
                    ->placeholder('Ej: LOTE2025A'),

                Forms\Components\DatePicker::make('fecha_caducidad')
                    ->label('Fecha de Caducidad (Opcional)')
                    ->nullable()
                    ->displayFormat('d/m/Y')
                    ->placeholder('DD/MM/AAAA'),

                Forms\Components\TextInput::make('ubicacion_almacen')
                    ->label('Ubicación en Almacén (Opcional)')
                    ->nullable()
                    ->maxLength(100)
                    ->placeholder('Ej: Estante A-3, Pasillo 2'),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VStockDetalle::query())
            ->columns([
                Tables\Columns\TextColumn::make('stock_id') 
                    ->label('ID Stock')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('producto_nombre') 
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->url(fn (VStockDetalle $record): ?string => $record->producto_id ? ProductoResource::getUrl('edit', ['record' => $record->producto_id]) : null)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('cantidad_disponible')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lote_numero') 
                    ->label('Lote')
                    ->searchable()
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('fecha_caducidad') 
                    ->label('Caducidad')
                    ->date('d/m/Y')
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('ubicacion_almacen') 
                    ->label('Ubicación')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('ultima_actualizacion_stock') 
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_creado_en') 
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // Si quieres mostrar 'stock_actualizado_en', añádelo aquí también
                // Tables\Columns\TextColumn::make('stock_actualizado_en')
                //     ->label('Últ. Modif. Stock')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('producto_id') // <--- Filtra usando producto_id de la vista
                    ->label('Filtrar por Producto')
                    // Para el filtro, si quieres usar la relación del modelo VStockDetalle (si la defines)
                    // o puedes cargar las opciones manualmente si es más simple:
                    ->options(
                        \App\Models\Producto::pluck('nombre', 'id')->all()
                    )
                    // Si definiste la relación 'productoOriginal' en VStockDetalle:
                    // ->relationship('productoOriginal', 'nombre')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('fecha_caducidad') // <--- Columna de la vista (mismo nombre)
                    ->form([
                        Forms\Components\DatePicker::make('caducado_desde')
                            ->label('Caducado desde'),
                        Forms\Components\DatePicker::make('caducado_hasta')
                            ->label('Caducado hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['caducado_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_caducidad', '>=', $date),
                            )
                            ->when(
                                $data['caducado_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_caducidad', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation() 
                    ->action(function (VStockDetalle $record) { 
                        $dbEditorConnection = DB::connection('mysql_editor');
                        try {
                            $stockIdParaEliminar = $record->stock_id;
                            $dbEditorConnection->statement(
                                "CALL sp_eliminar_stock(?, @success, @message)",
                                [$stockIdParaEliminar]
                            );
                            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");


                            if ($result && $result->success) {
                                Notification::make()
                                    ->title('¡Eliminado!')
                                    ->body($result->message ?: 'La entrada de stock ha sido eliminada correctamente.')
                                    ->success()
                                    ->send();
                            } else {
                                $errorMessage = 'Error desconocido desde el SP.';
                                if ($result && isset($result->message)) {
                                    $errorMessage = $result->message;
                                } elseif (!$result) {
                                    $errorMessage = 'El SP no devolvió un resultado (variables @success, @message no recuperadas).';
                                }

                                Notification::make()
                                    ->title('Error al Eliminar Stock')
                                    ->body($errorMessage)
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                           
                            report($e); 
                            Notification::make()
                                ->title('Error Inesperado')
                                ->body('Ocurrió un problema técnico al intentar eliminar la entrada de stock: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
              
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
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }

    // Opcional: Para definir un título de registro más descriptivo
    // La firma del método debe ser compatible con Filament\Resources\Resource
    public static function getRecordTitle(?Model $record): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        if (!$record) {
            return null;
        }
        if ($record instanceof Stock && $record->producto) {
            return 'Stock de ' . $record->producto->nombre . ($record->lote_numero ? ' (Lote: ' . $record->lote_numero . ')' : '');
        }
        return parent::getRecordTitle($record);
    }
}
