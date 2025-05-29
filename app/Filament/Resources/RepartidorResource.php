<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepartidorResource\Pages;
use App\Filament\Resources\RepartidorResource\RelationManagers;
use App\Models\Repartidor;
use App\Models\User; // Para el selector de usuario
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RepartidorResource extends Resource
{
    protected static ?string $model = Repartidor::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck'; // Icono para repartidores
    protected static ?string $modelLabel = 'Repartidor';
    protected static ?string $pluralModelLabel = 'Repartidores';
    protected static ?string $recordTitleAttribute = 'nombre_alias';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Usuario Asociado (Opcional)')
                    ->relationship(name: 'user', titleAttribute: 'name') // Asume que User tiene 'name'
                    ->searchable()
                    ->preload()
                    ->placeholder('Seleccionar un usuario existente para este repartidor')
                    ->unique(table: 'repartidores', column: 'user_id', ignoreRecord: true) // user_id debe ser único
                    ->helperText('Si este repartidor tiene una cuenta de usuario en el sistema.'),

                Forms\Components\TextInput::make('nombre_alias')
                    ->label('Nombre o Alias del Repartidor')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->placeholder('Ej: Repartidor Veloz 01'),

                Forms\Components\TextInput::make('vehiculo_descripcion')
                    ->label('Descripción del Vehículo')
                    ->nullable()
                    ->maxLength(255)
                    ->placeholder('Ej: Motocicleta Honda Roja, Placa XYZ-123'),

                Forms\Components\TextInput::make('zona_operativa_preferida')
                    ->label('Zona Operativa Preferida')
                    ->nullable()
                    ->maxLength(255)
                    ->placeholder('Ej: Zona Centro, Colonias del Sur'),

                Forms\Components\Toggle::make('disponible')
                    ->label('Disponible para Entregas')
                    ->default(true)
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->onColor('success')
                    ->offColor('danger'),

                Forms\Components\TextInput::make('calificacion_promedio')
                    ->label('Calificación Promedio')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(5)
                    ->step(0.01) // Para decimales
                    ->readOnly() // Generalmente se calcula, no se edita directamente
                    ->disabled() // Para que no se pueda modificar
                    ->helperText('Este campo se actualiza automáticamente.')
                    ->nullable(),

                Forms\Components\TextInput::make('numero_contacto_cifrado')
                    ->label('Número de Contacto (Simulado/Cifrado)')
                    ->nullable()
                    ->maxLength(255)
                    ->placeholder('Información de contacto'),
            ])->columns(2); // Dos columnas para el formulario
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_alias')
                    ->label('Alias')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name') // Muestra el nombre del usuario asociado
                    ->label('Usuario del Sistema')
                    ->default('N/A') // Si no hay usuario asociado
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('disponible')
                    ->label('Disponible')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehiculo_descripcion')
                    ->label('Vehículo')
                    ->limit(30)
                    ->tooltip(fn (Repartidor $record): ?string => $record->vehiculo_descripcion)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zona_operativa_preferida')
                    ->label('Zona Preferida')
                    ->limit(30)
                    ->tooltip(fn (Repartidor $record): ?string => $record->zona_operativa_preferida)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('calificacion_promedio')
                    ->label('Calificación')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->default('N/A'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('disponible')
                    ->label('Disponibilidad')
                    ->trueLabel('Sí, disponible')
                    ->falseLabel('No disponible')
                    ->placeholder('Todos'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Filtrar por Usuario')
                    ->relationship('user', 'name') // Asume que el modelo User tiene 'name'
                    ->searchable()
                    ->preload()
                    ->placeholder('Seleccionar usuario'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Aquí podrías añadir RelationManagers, por ejemplo, para ver los pedidos asignados
            // RelationManagers\PedidosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepartidores::route('/'),
            'create' => Pages\CreateRepartidor::route('/create'),
            'edit' => Pages\EditRepartidor::route('/{record}/edit'),
            // 'view' => Pages\ViewRepartidor::route('/{record}'), // Si quieres una página de vista dedicada
        ];
    }
}
