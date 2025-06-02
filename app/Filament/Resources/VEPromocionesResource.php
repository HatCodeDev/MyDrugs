<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VEPromocionesResource\Pages;
// use App\Filament\Resources\VEPromocionesResource\RelationManagers; // Descomentar si se necesitan relation managers
use App\Models\VEPromociones; // Importa tu modelo de vista
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VEPromocionesResource extends Resource
{
    protected static ?string $model = VEPromociones::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent'; // Ícono sugerido para promociones

    protected static ?string $navigationGroup = 'Estadísticas'; // Grupo de navegación sugerido

    protected static ?string $modelLabel = 'Rendimiento de Promoción';

    protected static ?string $pluralModelLabel = 'Rendimiento de Promociones';


    public static function form(Form $form): Form
    {
        // Generalmente no se define un formulario para vistas de solo lectura
        // a menos que quieras usarlo para filtros avanzados o una página de "Ver" muy personalizada.
        return $form
            ->schema([
                // Podrías añadir campos aquí si creas una página de "Ver" personalizada
                // Forms\Components\TextInput::make('codigo_promocion')->disabled(),
                // Forms\Components\Textarea::make('descripcion_promocion')->disabled(),
                // ... y así sucesivamente para todos los campos que quieras mostrar en un detalle.
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('promocion_id')
                    ->label('ID Promoción')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('codigo_promocion')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion_promocion')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50) // Limitar para que no ocupe mucho espacio en la tabla
                    ->tooltip(fn(VEPromociones $record): ?string => $record->descripcion_promocion), // Mostrar completo en tooltip
                Tables\Columns\TextColumn::make('tipo_descuento')
                    ->label('Tipo Descuento')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PORCENTAJE' => 'info',
                        'MONTO_FIJO' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor_descuento')
                    ->label('Valor Descuento')
                    ->money('MXN') 
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y') // Formato de fecha
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->date('d/m/Y') // Formato de fecha
                    ->placeholder('N/A') // Si es NULL
                    ->sortable(),
                Tables\Columns\IconColumn::make('promocion_activa')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero_total_usos')
                    ->label('Total Usos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_descuento_generado')
                    ->label('Total Descuento (€)')
                    ->money('MXN') // Asumiendo euros
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto_minimo_pedido')
                    ->label('Monto Mín. Pedido (€)')
                    ->money('MXN') // Asumiendo euros
                    ->placeholder('N/A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria_aplicable_nombre')
                    ->label('Categoría Aplicable')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('producto_aplicable_nombre')
                    ->label('Producto Aplicable')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_descuento')
                    ->options([
                        'PORCENTAJE' => 'Porcentaje',
                        'MONTO_FIJO' => 'Monto Fijo',
                    ]),
                Tables\Filters\TernaryFilter::make('promocion_activa')
                    ->label('Estado de la Promoción')
                    ->boolean()
                    ->trueLabel('Activas')
                    ->falseLabel('Inactivas')
                    ->native(false), // Usar selects en lugar de botones
                Tables\Filters\Filter::make('fecha_inicio')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_inicio_desde')->label('Inicio Desde'),
                        Forms\Components\DatePicker::make('fecha_inicio_hasta')->label('Inicio Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_inicio_desde'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_inicio', '>=', $date),
                            )
                            ->when(
                                $data['fecha_inicio_hasta'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha_inicio', '<=', $date),
                            );
                    }),
            ])
            ->actions([
               
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
                // Probablemente no necesites acciones en lote para vistas de solo lectura.
            ])
            ->defaultSort('numero_total_usos', 'desc'); // Ordenar por los más usados por defecto
    }

    public static function getRelations(): array
    {
        return [
            // Define relation managers si los necesitas.
            // Para una vista de solo lectura, es menos común.
        ];
    }

    public static function getPages(): array
    {
        // Si solo quieres la página de listado y, opcionalmente, la de visualización:
        return [
            'index' => Pages\ListVEPromociones::route('/'),
        ];
        // Si no quieres una página de "Ver" separada y ViewAction abre un modal,
        // podrías quitar la ruta 'view'.
    }
}
