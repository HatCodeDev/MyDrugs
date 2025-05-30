<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromocionResource\Pages;
use App\Models\Promocion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromocionResource extends Resource
{
    protected static ?string $model = Promocion::class;

    protected static ?string $navigationIcon = 'heroicon-o-percent-badge';
    protected static ?string $navigationLabel = 'Promociones';
    protected static ?string $pluralModelLabel = 'Promociones';
    protected static ?string $modelLabel = 'Promoción';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo_promocion')
                    ->label('Código de Promoción')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                Forms\Components\Textarea::make('descripcion')
                    ->label('Descripción')
                    ->nullable()
                    ->maxLength(65535),

                Forms\Components\Select::make('tipo_descuento')
                    ->label('Tipo de Descuento')
                    ->required()
                    ->options([
                        'PORCENTAJE' => 'Porcentaje',
                        'MONTO_FIJO' => 'Monto Fijo',
                    ]),

                Forms\Components\TextInput::make('valor_descuento')
                    ->label('Valor del Descuento')
                    ->required()
                    ->numeric()
                    ->step(0.01),

                Forms\Components\DateTimePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->required(),

                Forms\Components\DateTimePicker::make('fecha_fin')
                    ->label('Fecha de Fin')
                    ->nullable(),

                Forms\Components\TextInput::make('usos_maximos_global')
                    ->label('Usos Máximos Globales')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('usos_maximos_por_usuario')
                    ->label('Usos por Usuario')
                    ->numeric()
                    ->default(1)
                    ->nullable(),

                Forms\Components\Toggle::make('activo')
                    ->label('¿Activa?')
                    ->default(true),

                Forms\Components\Select::make('aplicable_a_categoria_id')
                    ->label('Categoría Aplicable')
                    ->relationship('categoriaAplicable', 'nombre')
                    ->searchable()
                    ->nullable(),

                Forms\Components\Select::make('aplicable_a_producto_id')
                    ->label('Producto Aplicable')
                    ->relationship('productoAplicable', 'nombre')
                    ->searchable()
                    ->nullable(),

                Forms\Components\TextInput::make('monto_minimo_pedido')
                    ->label('Monto Mínimo del Pedido')
                    ->numeric()
                    ->step(0.01)
                    ->nullable(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo_promocion')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo_descuento')
                    ->label('Tipo'),

                Tables\Columns\TextColumn::make('valor_descuento')
                    ->label('Descuento'),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_descuento')
                    ->label('Tipo de Descuento')
                    ->options([
                        'PORCENTAJE' => 'Porcentaje',
                        'MONTO_FIJO' => 'Monto Fijo',
                    ]),
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Activa'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromocions::route('/'),
            'create' => Pages\CreatePromocion::route('/create'),
            'edit' => Pages\EditPromocion::route('/{record}/edit'),
        ];
    }
}
