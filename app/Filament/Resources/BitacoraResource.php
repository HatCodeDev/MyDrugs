<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BitacoraResource\Pages;
use App\Models\Bitacora;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString; // Importa HtmlString

class BitacoraResource extends Resource
{
    protected static ?string $model = Bitacora::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('accion')
                    ->label('Acción')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('referencia_entidad')
                    ->label('Entidad')
                    ->sortable(),

                Tables\Columns\TextColumn::make('referencia_id')
                    ->label('ID Ref.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_evento')
                    ->label('Fecha del Evento')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // ... (tus filtros no cambian)
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name'),

                Tables\Filters\SelectFilter::make('referencia_entidad')
                    ->label('Entidad Referenciada')
                    ->options(
                        Bitacora::query()
                            ->select('referencia_entidad')
                            ->distinct()
                            ->pluck('referencia_entidad', 'referencia_entidad')
                    ),
            ])
            ->actions([
                
            ])
            ->bulkActions([
               
            ]);
    }

    // ... (getRelations y getPages no cambian)
    public static function getRelations(): array
    {
        return [
            // Puedes añadir relaciones si tu modelo Bitacora tiene más conexiones
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBitacoras::route('/'),
        ];
    }
}