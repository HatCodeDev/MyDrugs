<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepartidorResource\Pages;
use App\Models\Repartidor;             // Modelo Eloquent base
use App\Models\VRepartidoresDetalle;   // Modelo Eloquent para tu vista
use App\Models\User;                   // Para el selector de user_id
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
// use Illuminate\Support\Facades\Log; // Si necesitas logging adicional

class RepartidorResource extends Resource
{
    protected static ?string $model = Repartidor::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck'; // Ícono para repartidores
    protected static ?string $modelLabel = 'Repartidor';
    protected static ?string $pluralModelLabel = 'Repartidores';
    protected static ?string $recordTitleAttribute = 'nombre_alias'; // Para títulos en Filament

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Select::make('user_id')
                //     ->relationship('user', 'name') // Asume relación 'user' en Repartidor y 'name' en User
                //     ->searchable()
                //     ->preload()
                //     ->nullable()
                //     // La unicidad de user_id en repartidores ya está en la BD y se validará en el SP
                //     ->label('Usuario Asociado (Opcional)'),
                Forms\Components\TextInput::make('nombre_alias')
                    ->required()
                    ->maxLength(100)
                    // La unicidad del alias ya está en la BD y se validará en el SP
                    ->label('Nombre o Alias'),
                Forms\Components\TextInput::make('vehiculo_descripcion')
                    ->nullable()
                    ->maxLength(255)
                    ->label('Descripción del Vehículo'),
                Forms\Components\TextInput::make('zona_operativa_preferida')
                    ->nullable()
                    ->maxLength(255)
                    ->label('Zona Operativa Preferida'),
                Forms\Components\Toggle::make('disponible')
                    ->required()
                    ->default(true)
                    ->label('Disponible para Entregas'),
                Forms\Components\TextInput::make('calificacion_promedio')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->maxValue(5) // Asumiendo una escala de 0-5
                    ->step(0.01) // Para permitir decimales
                    ->label('Calificación Promedio')
                    ->helperText('Este campo podría ser calculado automáticamente en el futuro.'),
                Forms\Components\TextInput::make('numero_contacto_cifrado')
                    ->nullable()
                    ->maxLength(255)
                    ->label('Número de Contacto (Simulación Cifrado)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VRepartidoresDetalle::query()) // Usa la VISTA para el listado
            ->columns([
                Tables\Columns\TextColumn::make('repartidor_id')
                    ->label('ID Rep.')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre_alias')
                    ->label('Alias')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_usuario') // De la vista (join con users)
                    ->label('Usuario Sistema')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email_usuario') // De la vista
                    ->label('Email Usuario')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('disponible') // Muestra como ícono booleano
                    ->label('Disponible')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehiculo_descripcion')
                    ->label('Vehículo')
                    ->limit(30)
                    ->tooltip('Ver descripción completa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('zona_operativa_preferida')
                    ->label('Zona Preferida')
                    ->limit(30)
                    ->tooltip('Ver zona completa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('calificacion_promedio')
                    ->label('Calificación')
                    ->formatStateUsing(fn (?string $state): string => $state ? number_format((float)$state, 2) : '-') // Formatea a 2 decimales, muestra '-' si es null
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignRight(), // Es común alinear números a la derecha
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Registro')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Model $record) { // $record es VRepartidoresDetalle
                        try {
                            DB::statement(
                                "CALL sp_eliminar_repartidor(?, @success, @message)",
                                [$record->repartidor_id] // ID del repartidor desde la vista
                            );
                            $result = DB::selectOne("SELECT @success AS success, @message AS message");

                            if ($result && $result->success) {
                                Notification::make()
                                    ->title($result->message ?: '¡Repartidor eliminado exitosamente!')
                                    ->success()->send();
                            } else {
                                Notification::make()
                                    ->title('Error al eliminar repartidor')
                                    ->body($result->message ?: 'No se pudo eliminar el repartidor.')
                                    ->danger()->send();
                            }
                        } catch (\Exception $e) {
                            report($e);
                            Notification::make()
                                ->title('Error inesperado en la eliminación')
                                ->body('Ocurrió un problema técnico: ' . $e->getMessage())
                                ->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Considerar personalizar si necesitas usar SPs para eliminación en masa.
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Aquí podrías tener RelationManagers, por ejemplo, para listar pedidos asignados a este repartidor.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepartidores::route('/'),
            'create' => Pages\CreateRepartidor::route('/create'),
            'edit' => Pages\EditRepartidor::route('/{record}/edit'),
        ];
    }
}