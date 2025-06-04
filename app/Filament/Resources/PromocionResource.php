<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromocionResource\Pages;
use App\Models\Promocion;
use App\Models\VPromocionesDetalle;
use App\Models\Categoria; // Para el selector de categorías
use App\Models\Producto;  // Para el selector de productos
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Forms\Get; // Para lógica condicional en el formulario

class PromocionResource extends Resource
{
    protected static ?string $model = Promocion::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift'; // Ícono para promociones
    protected static ?string $modelLabel = 'Promoción';
    protected static ?string $pluralModelLabel = 'Promociones';
    protected static ?string $recordTitleAttribute = 'codigo_promocion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo_promocion')
                    ->required()
                    ->maxLength(50)
                    ->label('Código de Promoción'),
                Forms\Components\Textarea::make('descripcion')
                    ->nullable()
                    ->columnSpanFull()
                    ->label('Descripción'),
                Forms\Components\Select::make('tipo_descuento')
                    ->options([
                        'PORCENTAJE' => 'Porcentaje',
                        'MONTO_FIJO' => 'Monto Fijo',
                    ])
                    ->required()
                    ->reactive() // Para que otros campos puedan reaccionar a su cambio
                    ->label('Tipo de Descuento'),
                Forms\Components\TextInput::make('valor_descuento')
                    ->required()
                    ->numeric()
                    ->prefix(fn (Get $get) => $get('tipo_descuento') === 'PORCENTAJE' ? '%' : '$') // Prefijo dinámico
                    ->label('Valor del Descuento'),
                Forms\Components\DateTimePicker::make('fecha_inicio')
                    ->required()
                    ->label('Fecha de Inicio'),
                Forms\Components\DateTimePicker::make('fecha_fin')
                    ->nullable()
                    ->minDate(fn (Get $get) => $get('fecha_inicio')) // fecha_fin > fecha_inicio
                    ->label('Fecha de Fin (Opcional)'),
                Forms\Components\TextInput::make('usos_maximos_global')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->label('Usos Máximos Globales (Opcional)'),
                Forms\Components\TextInput::make('usos_maximos_por_usuario')
                    ->numeric()
                    ->nullable()
                    ->default(1)
                    ->minValue(0)
                    ->label('Usos Máximos por Usuario (Opcional)'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Activa'),
                Forms\Components\Select::make('aplicable_a_categoria_id')
                    ->relationship('categoriaAplicable', 'nombre')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Aplicable a Categoría (Opcional)')
                    ->disabled(fn (Get $get): bool => filled($get('aplicable_a_producto_id'))) // Deshabilitar si producto está lleno
                    ->helperText('Dejar vacío si no aplica o si es para un producto específico.'),
                Forms\Components\Select::make('aplicable_a_producto_id')
                    ->relationship('productoAplicable', 'nombre')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('Aplicable a Producto (Opcional)')
                    ->disabled(fn (Get $get): bool => filled($get('aplicable_a_categoria_id'))) // Deshabilitar si categoría está llena
                    ->helperText('Dejar vacío si no aplica o si es para una categoría específica.'),
                Forms\Components\TextInput::make('monto_minimo_pedido')
                    ->numeric()
                    ->nullable()
                    ->prefix('$')
                    ->minValue(0)
                    ->label('Monto Mínimo del Pedido (Opcional)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VPromocionesDetalle::query())
            ->columns([
                Tables\Columns\TextColumn::make('promocion_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo_promocion')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_descuento')
                    ->label('Tipo Dto.')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PORCENTAJE' => 'info',
                        'MONTO_FIJO' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor_descuento')
                    ->label('Valor Dto.')
                    ->money(fn (VPromocionesDetalle $record) => $record->tipo_descuento === 'MONTO_FIJO' ? 'MXN' : null) // Solo aplica 'money' si es monto fijo
                    ->formatStateUsing(fn (VPromocionesDetalle $record, $state): string => 
                        $record->tipo_descuento === 'PORCENTAJE' ? number_format($state, 2) . '%' : '$' . number_format($state, 2)
                    )
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Indefinido')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_categoria_aplicable')
                    ->label('Categoría Aplic.')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nombre_producto_aplicable')
                    ->label('Producto Aplic.')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('usos_maximos_global')
                    ->label('Usos Glob.')
                    ->placeholder('Ilimitado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Model $record) { // $record es VPromocionesDetalle
                        $dbEditorConnection = DB::connection('mysql_editor');
                        try {
                           $dbEditorConnection->statement(
                                "CALL sp_eliminar_promocion(?, @success, @message)",
                                [$record->promocion_id]
                            );
                            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

                            if ($result && $result->success) {
                                Notification::make()
                                    ->title($result->message ?: '¡Promoción eliminada exitosamente!')
                                    ->success()->send();
                            } else {
                                Notification::make()
                                    ->title('Error al eliminar promoción')
                                    ->body($result->message ?: 'No se pudo eliminar la promoción.')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromociones::route('/'),
            'create' => Pages\CreatePromocion::route('/create'),
            'edit' => Pages\EditPromocion::route('/{record}/edit'),
        ];
    }
}