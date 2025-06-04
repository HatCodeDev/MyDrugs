<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoResource\Pages;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\VPedidoDetalle;
use Filament\Infolists; 
use Filament\Infolists\Infolist;
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
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

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
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->options(Producto::query()->pluck('nombre', 'id'))
                                    ->searchable()
                                    ->preload()
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
                                    ->prefix('$')
                                    ->columnSpan(2),
                                TextInput::make('subtotal')
                                    ->label('Subtotal (Item)')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('$')
                                    ->columnSpan(2)
                                    ->helperText('Se calcula automáticamente al guardar (Trigger).'),
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
                            ->disabled() 
                            ->helperText('Se calcula automáticamente.'),
                        TextInput::make('descuento_aplicado')
                            ->numeric()
                            ->prefix('$')
                            ->disabled() 
                            ->helperText('Se calcula automáticamente.'),
                        TextInput::make('total_pedido')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->helperText('Se calcula automáticamente.'),
                    ])
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información Principal del Pedido')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('id')->label('ID Pedido'),
                        Infolists\Components\TextEntry::make('cliente.name')->label('Cliente'), 
                        Infolists\Components\TextEntry::make('cliente.email')->label('Email Cliente'),
                        Infolists\Components\TextEntry::make('fecha_pedido')->dateTime('d/m/Y H:i:s'),
                        Infolists\Components\TextEntry::make('estado_pedido')
                            ->badge()
                            ->color(fn(string $state): string => match (strtolower($state)) {
                                'pendiente' => 'warning',
                                'procesando' => 'info',
                                'en_ruta' => 'primary',
                                'entregado' => 'success',
                                'cancelado' => 'danger',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('codigo_seguimiento')->label('Cód. Seguimiento')->copyable()->placeholder('N/A'),
                    ]),
                Infolists\Components\Section::make('Entrega y Pago')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('direccion_entrega_cifrada')->label('Dirección Entrega')->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('punto_entrega_especial')->label('Punto Entrega Esp.')->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('fecha_estimada_entrega')->dateTime('d/m/Y H:i')->label('Entrega Estimada')->placeholder('N/A'),
                        Infolists\Components\TextEntry::make('repartidor.nombre_alias')->label('Repartidor')->placeholder('N/A'), 
                        Infolists\Components\TextEntry::make('metodoPago.nombre_metodo')->label('Método Pago'), 
                    ]),
                Infolists\Components\Section::make('Notas y Promoción')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('notas_cliente')->label('Notas Cliente')->placeholder('N/A')->columnSpanFull(),
                        Infolists\Components\TextEntry::make('promocion.codigo_promocion')->label('Promoción Aplicada')->placeholder('N/A'), 
                        Infolists\Components\TextEntry::make('promocion.valor_descuento')
                            ->label('Valor Descuento Promo')
                            ->money('MXN') 
                            ->visible(fn($record) => $record->promocion_id !== null) 
                            ->placeholder('N/A'),
                    ]),
                Infolists\Components\Section::make('Totales')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal_pedido')->money('MXN'),
                        Infolists\Components\TextEntry::make('descuento_aplicado')->money('MXN'),
                        Infolists\Components\TextEntry::make('total_pedido')->money('MXN')->weight('bold'),
                    ]),
                Infolists\Components\Section::make('Artículos del Pedido')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('detalles') 
                            ->schema([
                                Infolists\Components\TextEntry::make('producto.nombre')->label('Producto'),
                                Infolists\Components\TextEntry::make('cantidad')->label('Cant.'),
                                Infolists\Components\TextEntry::make('precio_unitario_en_pedido')->label('Precio Unit.')->money('MXN'),
                                Infolists\Components\TextEntry::make('subtotal')->label('Subtotal Item')->money('MXN'),
                            ])
                            ->columns(4)
                            ->grid(2) 
                            ->label(false), 
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VPedidoDetalle::query()) 
            ->columns([
                Tables\Columns\TextColumn::make('pedido_id') 
                    ->label('ID Pedido')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->label('Fecha')
                    ->date('d/m/Y') 
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente_nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->tooltip(fn($record) => $record->cliente_nombre),
                Tables\Columns\TextColumn::make('producto_nombre') 
                    ->label('Item Principal') 
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(fn($record) => $record->producto_nombre),
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cant.')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detalle_subtotal')
                    ->label('Subtotal Item')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orden_total') 
                    ->label('Total Pedido')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_pedido') 
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'pendiente' => 'warning',
                        'procesando' => 'info',
                        'en_ruta' => 'primary',
                        'entregado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('detalle_id')
                    ->label('ID Detalle')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('repartidor_nombre_alias')
                    ->label('Repartidor')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('metodo_pago_nombre')
                    ->label('Método Pago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
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
                Tables\Actions\ViewAction::make()
                    ->url(fn(VPedidoDetalle $record): string => PedidoResource::getUrl('view', ['record' => $record->pedido_id])),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation() 
                    ->action(function (VPedidoDetalle $record) { 
                        try {
                            $dbEditorConnection = DB::connection('mysql_editor');
                            $dbEditorConnection->statement(
                                "CALL sp_eliminar_pedido(?, @success, @message)",
                                [$record->pedido_id] 
                            );
                            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");
                            if ($result && $result->success) {
                                Notification::make()
                                    ->title('¡Eliminado!')
                                    ->body($result->message ?: 'El pedido ha sido eliminado correctamente.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Error al Eliminar')
                                    ->body($result->message ?: 'No se pudo eliminar el pedido (según SP).')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {

                            Notification::make()
                                ->title('Error Inesperado')
                                ->body('Ocurrió un problema técnico al intentar eliminar el pedido: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            'view' => Pages\ViewPedido::route('/{record}'),
        ];
    }
}
