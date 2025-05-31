<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductDetailExtendedViewResource\Pages;
use App\Models\ProductDetailExtendedView;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class ProductDetailExtendedViewResource extends Resource
{
    protected static ?string $model = ProductDetailExtendedView::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells'; 
    protected static ?string $navigationLabel = 'Productos Extendidos';
    protected static ?string $modelLabel = 'Resumen de Producto'; 
    protected static ?string $pluralModelLabel = 'Resúmenes de Productos';

    protected static ?string $slug = 'resumen-productos-extendido';

    protected static ?string $navigationGroup = 'Vistas';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_id')
                    ->label('ID Producto')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false), 
                Tables\Columns\ImageColumn::make('main_image_url')
                    ->label('Imagen')
                    ->disk('public') 
                    ->height(60)
                    ->circular()
                    ->square()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('precio_unitario')
                    ->label('Precio')
                    ->money('mxn') 
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('total_stock_available')
                    ->label('Stock')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('average_rating')
                    ->label('Rating Prom.')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'info',
                        4 => 'success',
                        5 => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => "{$state} " . ($state === 1 ? 'estrella' : 'estrellas'))
                    ->label('Puntuación')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('rating_count')
                    ->label('Nº Ratings')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('product_is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('product_created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 
                 Tables\Columns\TextColumn::make('product_updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 
                 Tables\Columns\TextColumn::make('product_description')
                    ->label('Descripción')
                    ->limit(50) 
                    ->tooltip(fn (ProductDetailExtendedView $record): ?string => $record->product_description) 
                    ->toggleable(isToggledHiddenByDefault: true),
                 Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad Med.')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_name')
                    ->label('Categoría')
                    ->options(fn() => \App\Models\Categoria::pluck('nombre', 'nombre')->all())
                    ->attribute('category_name'),
                Tables\Filters\TernaryFilter::make('product_is_active')->label('Estado Activo'),
            ])
            ->defaultSort('product_id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductDetailExtendedViews::route('/'),
        ];
    }
}