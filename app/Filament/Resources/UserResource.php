<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\VUserDetalle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ingresa el nombre completo del usuario'),

                Forms\Components\TextInput::make('email')
                    ->label('Correo Electrónico')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true) 
                    ->maxLength(255)
                    ->placeholder('usuario@ejemplo.com'),

                Forms\Components\TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn(string $operation): bool => $operation === 'create') 
                    ->dehydrateStateUsing(function (?string $state): ?string {
                        return filled($state) ? Hash::make($state) : null;
                    })
                    ->dehydrated(fn(?string $state): bool => filled($state)) 
                    ->maxLength(255)
                    ->placeholder('••••••••')
                    ->revealable(), 
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->placeholder('Selecciona uno o más roles')

            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VUserDetalle::query()) 
            ->columns([
                Tables\Columns\TextColumn::make('user_name') 
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_email') 
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles_list')
                    ->label('Roles')
                    ->badge()
                    ->color('primary')
                    ->searchable() 
                    ->sortable(), 
                Tables\Columns\TextColumn::make('user_created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user_updated_at') 
                    ->label('Última Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('roles') 
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->label('Filtrar por Rol (Puede requerir ajuste)'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                ->action(function (VUserDetalle $record) {
                    $user = User::find($record->user_id);
                    if (!$user) {
                        Notification::make()
                            ->title('Error al eliminar')
                            ->body('Usuario no encontrado.')
                            ->danger()
                            ->send();
                        return;
                    }
                    if ($user->pedidos()->exists()) {
                  
                        Notification::make()
                            ->title('Eliminación Fallida')
                            ->body('Este usuario no puede ser eliminado porque tiene pedidos asociados.')
                            ->danger()
                            ->send();
                        return; 
                    }
                    if ($user->repartidor()->exists()) {
                        Notification::make()
                            ->title('Eliminación Fallida')
                            ->body('Este usuario no puede ser eliminado porque está registrado como repartidor.')
                            ->danger()
                            ->send();
                        return;
                    }
                    try {
                        $user->delete();
                        Notification::make()
                            ->title('Usuario eliminado correctamente')
                            ->success()
                            ->send();
                    } catch (\Illuminate\Database\QueryException $e) {
                        Notification::make()
                            ->title('Error de Base de Datos')
                            ->body('No se pudo eliminar el usuario. Puede que aún tenga datos relacionados no contemplados o exista otro problema.')
                            ->danger()
                            ->send();
                    }
                }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
