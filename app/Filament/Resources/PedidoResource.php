<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Models\Pedido;
use App\Models\Producto;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth; 
use Filament\Forms\Components\Hidden;

class PedidoResource extends Resource
{
    protected static ?string $model = Pedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $recordTitleAttribute = 'codigo_seguimiento';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Pedido')
                    ->columns(2)
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn() => Auth::id())
                            ->required(),
                        Select::make('repartidor_id')
                            ->label('Repartidor')
                            ->relationship('repartidor', 'nombre_alias')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('metodo_pago_id')
                            ->label('Método de Pago')
                            ->relationship('metodoPago', 'nombre_metodo')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('promocion_id')
                            ->label('Promoción')
                            ->relationship('promocion', 'codigo_promocion')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('direccion_entrega_cifrada')
                            ->label('Dirección de Entrega')
                            ->nullable(),
                        TextInput::make('punto_entrega_especial')
                            ->label('Punto de Entrega Especial')
                            ->nullable(),
                        Select::make('estado_pedido')
                            ->options([
                                'PENDIENTE' => 'Pendiente',
                                'PROCESANDO' => 'Procesando',
                                'EN_RUTA' => 'En Ruta',
                                'ENTREGADO' => 'Entregado',
                                'CANCELADO' => 'Cancelado',
                            ])
                            ->default('PENDIENTE')
                            ->required(),
                        DateTimePicker::make('fecha_estimada_entrega')
                            ->label('Fecha Estimada de Entrega')
                            ->nullable(),
                        Textarea::make('notas_cliente')
                            ->label('Notas del Cliente')
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('codigo_seguimiento')
                            ->label('Código de Seguimiento')
                            ->nullable()
                            ->unique(Pedido::class, 'codigo_seguimiento', ignoreRecord: true),
                    ]),

                Section::make('Detalles del Pedido')
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship()
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->relationship('producto', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $producto = Producto::find($state);
                                        if ($producto) {
                                            $set('precio_unitario_en_pedido', $producto->precio_unitario);
                                        }
                                    })
                                    ->distinct()
                                    ->columnSpan(4),
                                TextInput::make('cantidad')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->reactive()
                                    ->columnSpan(2),
                                TextInput::make('precio_unitario_en_pedido')
                                    ->label('Precio Unitario')
                                    ->numeric()
                                    ->required()
                                    // ->disabled() // Si se autocompleta y no debe ser modificado
                                    ->prefix('$')
                                    ->columnSpan(2),
                                TextInput::make('subtotal')
                                    ->label('Subtotal (Item)')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('$')
                                    ->columnSpan(2)
                                    ->helperText('Se calcula automáticamente al guardar.'),
                            ])
                            ->columns(10)
                            ->addActionLabel('Añadir Producto')
                            ->defaultItems(1)
                            ->cloneable()
                            ->collapsible(),
                    ]),

                Section::make('Totales del Pedido')
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal_pedido')
                            ->numeric()
                            ->prefix('$')
                            ->disabled() // Calculado por trigger
                            ->helperText('Se calcula automáticamente.'),
                        TextInput::make('descuento_aplicado')
                            ->numeric()
                            ->prefix('$')
                            ->disabled() // Calculado por trigger 
                            ->helperText('Se calcula automáticamente.'),
                        TextInput::make('total_pedido')
                            ->numeric()
                            ->prefix('$')
                            ->disabled() // Calculado por trigger
                            ->helperText('Se calcula automáticamente.'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('cliente.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_pedido')
                    ->money('mxn')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_pedido')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'PROCESANDO' => 'info',
                        'EN_RUTA' => 'primary',
                        'ENTREGADO' => 'success',
                        'CANCELADO' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('repartidor.nombre_alias')
                    ->label('Repartidor')
                    ->sortable()
                    ->searchable()
                    ->placeholder('No asignado'),
                Tables\Columns\TextColumn::make('codigo_seguimiento')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_pedido')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'PROCESANDO' => 'Procesando',
                        'EN_RUTA' => 'En Ruta',
                        'ENTREGADO' => 'Entregado',
                        'CANCELADO' => 'Cancelado',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Considera si realmente quieres permitir borrar pedidos o solo cancelarlos
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
            // Si en el futuro necesitas una gestión más compleja de los detalles,
            // podrías crear un RelationManager aquí. Por ahora, el Repeater es suficiente.
            // RelationManagers\DetallesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPedidos::route('/'),
            'create' => Pages\CreatePedido::route('/create'),
            'edit' => Pages\EditPedido::route('/{record}/edit'),
        ];
    }
}
