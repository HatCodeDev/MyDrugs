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

class BitacoraResource extends Resource
{
    protected static ?string $model = Bitacora::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Selecciona un usuario'),

                Forms\Components\TextInput::make('accion')
                    ->label('Acción')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('descripcion_detallada')
                    ->label('Descripción Detallada')
                    ->maxLength(1000)
                    ->rows(4),

                Forms\Components\TextInput::make('referencia_entidad')
                    ->label('Entidad Referenciada')
                    ->maxLength(100)
                    ->placeholder('Ej. Producto, Pedido'),

                Forms\Components\TextInput::make('referencia_id')
                    ->label('ID de Referencia')
                    ->numeric()
                    ->placeholder('Ej. 42'),

                Forms\Components\DateTimePicker::make('fecha_evento')
                    ->label('Fecha del Evento')
                    ->required(),
            ])
            ->columns(2);
    }

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

                Tables\Columns\TextColumn::make('descripcion_detallada')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->descripcion_detallada),
            ])
            ->filters([
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
        return [
            // Puedes añadir relaciones si tu modelo Bitacora tiene más conexiones
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBitacoras::route('/'),
            'create' => Pages\CreateBitacora::route('/create'),
            'edit' => Pages\EditBitacora::route('/{record}/edit'),
        ];
    }
}
