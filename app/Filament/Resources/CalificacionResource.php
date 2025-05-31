<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CalificacionResource\Pages;
use App\Models\Calificacion; // Modelo base para el recurso
use App\Models\VCalificacionDetalle; // <--- AÑADE ESTE IMPORT para tu modelo de vista
use Illuminate\Database\Eloquent\Builder; // <--- AÑADE ESTE IMPORT para el tipado en modifyQueryUsing si lo usaras

// Tus otros uses:
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CalificacionResource extends Resource
{
    // ESTA LÍNEA NO CAMBIA. SIGUE SIENDO EL MODELO BASE Calificacion::class.
    protected static ?string $model = Calificacion::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $modelLabel = 'Calificación';
    protected static ?string $pluralModelLabel = 'Calificaciones';
    protected static ?string $recordTitleAttribute = 'id';


    // EL MÉTODO form() NO CAMBIA.
    public static function form(Form $form): Form
    {
        // ... (tu código de form existente, sin cambios) ...
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id())
                    ->required(),
                Forms\Components\Select::make('producto_id')
                    ->relationship('producto', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Producto')
                    ->rules([
                        fn (Get $get, ?Calificacion $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            $userId = $get('user_id');
                            if (!$userId && Auth::check()) {
                                $userId = Auth::id();
                            }
                            if ($userId) {
                                $query = Calificacion::query()
                                    ->where('producto_id', $value)
                                    ->where('user_id', $userId);
                                if ($record) {
                                    $query->where('id', '!=', $record->id);
                                }
                                if ($query->exists()) {
                                    $fail('Ya has calificado este producto.');
                                }
                            } elseif (!Auth::check()) {
                                $fail('Debes iniciar sesión para calificar un producto.');
                            }
                        },
                    ]),
                Forms\Components\TextInput::make('puntuacion')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->label('Puntuación (1-5)'),
                Forms\Components\Textarea::make('comentario')
                    ->nullable()
                    ->columnSpanFull()
                    ->label('Comentario'),
            ]);
    }

    // MÉTODO table() MODIFICADO:
    public static function table(Table $table): Table
    {
        return $table
            // AQUÍ LE DECIMOS A LA TABLA QUE USE LA VISTA COMO SU FUENTE DE DATOS PRINCIPAL
            ->query(VCalificacionDetalle::query())
            ->columns([
                // Columnas de la VISTA 'v_calificaciones_detalle'
                Tables\Columns\TextColumn::make('calificacion_id')
                    ->label('ID Cal.')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('producto_nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    
                    ,

                Tables\Columns\TextColumn::make('producto_precio_unitario')
                    ->label('Precio Prod.')
                    ->money('MXN')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usuario_nombre')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('usuario_email')
                    ->label('Email Usuario')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('puntuacion')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'danger', 2 => 'warning', 3 => 'info',
                        4 => 'success', 5 => 'success', default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => "{$state} " . ($state === 1 ? 'estrella' : 'estrellas'))
                    ->label('Puntuación'),

                Tables\Columns\TextColumn::make('comentario')
                    ->limit(50)
                    ->tooltip('Ver comentario completo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Comentario'),

                Tables\Columns\TextColumn::make('fecha_calificacion')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha Calificación'),

                Tables\Columns\TextColumn::make('calificacion_created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Creado'),

                Tables\Columns\TextColumn::make('calificacion_updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Actualizado'),
            ])
            ->filters([
                // ... tus filtros ...
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Model $record) { // $record aquí será una instancia de VCalificacionDetalle
                        try {
                            DB::statement(
                                "CALL sp_eliminar_calificacion(?, @success, @message)",
                                [$record->calificacion_id] // Usamos el calificacion_id de la vista
                            );
                            $result = DB::selectOne("SELECT @success AS success, @message AS message");
                            if ($result && $result->success) {
                                Notification::make()
                                    ->title($result->message ?: '¡Eliminación exitosa!')
                                    ->success()->send();
                            } else {
                                Notification::make()
                                    ->title('Error al eliminar')
                                    ->body($result->message ?: 'No se pudo eliminar la calificación.')
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

    // getRelations() y getPages() NO CAMBIAN
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalificaciones::route('/'),
            'create' => Pages\CreateCalificacion::route('/create'),
            'edit' => Pages\EditCalificacion::route('/{record}/edit'),
        ];
    }
}