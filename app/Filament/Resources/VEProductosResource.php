<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VEProductosResource\Pages;
// use App\Filament\Resources\VEProductosResource\RelationManagers; // Descomentar si es necesario
use App\Models\VEProductos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter; // Para filtros de categoría
use Filament\Tables\Filters\TernaryFilter; // Para filtros booleanos como 'producto_activo'

class VEProductosResource extends Resource
{
    protected static ?string $model = VEProductos::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube'; 

    protected static ?string $navigationGroup = 'Estadísticas'; 

    protected static ?string $modelLabel = 'Estadística de Producto';

    protected static ?string $pluralModelLabel = 'Estadísticas de Productos';


    public static function form(Form $form): Form
    {
        // Para vistas de solo lectura, el formulario es usualmente mínimo o se usa
        // para la página de "Ver" si se personaliza mucho.
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('producto_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('producto_nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn(VEProductos $record): ?string => $record->producto_nombre),
                Tables\Columns\TextColumn::make('categoria_nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('precio_unitario')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\IconColumn::make('producto_activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_disponible')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn (string $state): string => $state > 10 ? 'success' : ($state > 0 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('promedio_calificacion')
                    ->label('Calificación Prom.')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->icon('heroicon-s-star')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('total_ventas_generadas')
                    ->label('Ventas Generadas')
                    ->money('MXN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero_veces_en_pedidos')
                    ->label('Veces Pedido')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_unidades_vendidas')
                    ->label('Unidades Vendidas')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('categoria_nombre')
                    ->label('Categoría')
                    ->options(fn () => \App\Models\Categoria::pluck('nombre', 'nombre')->all()) // Asume que tienes un modelo Categoria
                    ->searchable(),
                TernaryFilter::make('producto_activo')
                    ->label('Estado del Producto')
                    ->boolean()
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos')
                    ->native(false),
                Tables\Filters\Filter::make('bajo_stock')
                    ->label('Bajo Stock (< 10 unidades)')
                    ->query(fn (Builder $query): Builder => $query->where('stock_disponible', '<', 10)),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // No EditAction ni DeleteAction para una vista de solo lectura de estadísticas
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
                // No acciones en lote para vistas de solo lectura
            ])
            ->defaultSort('total_ventas_generadas', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        // Para una vista de estadísticas, usualmente solo necesitas la lista y la vista individual.
        return [
            'index' => Pages\ListVEProductos::route('/'),
        ];
    }
}