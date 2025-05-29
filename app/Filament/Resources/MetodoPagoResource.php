<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetodoPagoResource\Pages;
use App\Models\MetodoPago;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MetodoPagoResource extends Resource
{
    protected static ?string $model = MetodoPago::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Métodos de Pago';
    protected static ?string $pluralModelLabel = 'Métodos de Pago';
    protected static ?string $modelLabel = 'Método de Pago';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_metodo')
                    ->label('Nombre del Método')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                Forms\Components\Textarea::make('descripcion_instrucciones')
                    ->label('Descripción e Instrucciones')
                    ->rows(3)
                    ->maxLength(65535)
                    ->nullable(),

                Forms\Components\TextInput::make('comision_asociada_porcentaje')
                    ->label('Comisión Asociada (%)')
                    ->numeric()
                    ->step(0.01)
                    ->default(0.00),

                Forms\Components\Toggle::make('activo')
                    ->label('¿Activo?')
                    ->default(true),

                Forms\Components\TextInput::make('logo_url')
                    ->label('URL del Logo')
                    
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_metodo')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('comision_asociada_porcentaje')
                    ->label('Comisión (%)')
                    ->sortable(),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')
                    ->trueLabel('Activos')
                    ->falseLabel('Inactivos'),
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
            'index' => Pages\ListMetodoPagos::route('/'),
            'create' => Pages\CreateMetodoPago::route('/create'),
            'edit' => Pages\EditMetodoPago::route('/{record}/edit'),
        ];
    }
}
