<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Filament\Resources\StockResource\RelationManagers;
use App\Models\Stock;
use App\Models\Producto; // Para el selector de producto
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model; // Asegúrate de importar la clase Model correcta

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box'; // Icono para stock

    protected static ?string $modelLabel = 'Entrada de Stock';
    protected static ?string $pluralModelLabel = 'Gestión de Stock';
    // protected static ?string $recordTitleAttribute = 'lote_numero'; // O combinar con producto


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('producto_id')
                    ->label('Producto')
                    ->relationship(name: 'producto', titleAttribute: 'nombre') // Asume que Producto tiene 'nombre'
                    ->searchable()
                    ->preload()
                    ->required()
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
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Stock $record): ?string => $record->producto ? ProductoResource::getUrl('edit', ['record' => $record->producto_id]) : null)
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('producto_id')
                    ->label('Filtrar por Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('fecha_caducidad')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
        // Asegurarse de que $record es una instancia de Stock
        if ($record instanceof Stock && $record->producto) {
            return 'Stock de ' . $record->producto->nombre . ($record->lote_numero ? ' (Lote: ' . $record->lote_numero . ')' : '');
        }
        // Llama al método padre si no es una instancia de Stock o no se puede generar el título personalizado
        return parent::getRecordTitle($record);
    }
}
