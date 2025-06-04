<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BitacoraResource\Pages;
use App\Models\Bitacora;             // Modelo Eloquent base
use App\Models\VBitacorasDetalle;   // Modelo Eloquent para tu vista
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Para truncar cadenas
use Filament\Infolists; // Para mostrar información en la página de vista
use Filament\Infolists\Infolist;

class BitacoraResource extends Resource
{
    protected static ?string $model = Bitacora::class; // Usamos el modelo base para la definición general

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Registro de Bitácora';
    protected static ?string $pluralModelLabel = 'Bitácoras';
    protected static ?string $recordTitleAttribute = 'accion'; // O 'id'

    /**
     * Aunque no vamos a "editar", la ViewAction usa el formulario por defecto para mostrar campos.
     * Podemos definir un Infolist para una mejor visualización.
     * Si no se define form(), la ViewAction podría intentar mostrar un formulario vacío o fallar.
     * Por simplicidad, no definiremos un form aquí, ya que nos enfocaremos en la tabla y una posible ViewAction.
     * Si se necesita una página de vista detallada, se usará un Infolist en ViewBitacora.php.
     */
    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             // Campos de solo lectura si se quisiera un "formulario" de vista
    //             Forms\Components\TextInput::make('accion')->disabled(),
    //             Forms\Components\DateTimePicker::make('fecha_evento')->disabled(),
    //             // ... otros campos ...
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VBitacorasDetalle::query()) // Usar la VISTA para el listado
            ->columns([
                Tables\Columns\TextColumn::make('bitacora_id')
                    ->label('ID Log')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_evento')
                    ->label('Fecha Evento')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('accion')
                    ->label('Acción')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'crear', 'insertar', 'nueva_categoria', 'nuevo_producto' => 'success',
                        'actualizar', 'modificar' => 'primary',
                        'eliminar', 'borrar' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('nombre_usuario') // De la vista (join con users)
                    ->label('Usuario')
                    ->placeholder('Sistema/Trigger')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('referencia_entidad')
                    ->label('Entidad')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('referencia_id')
                    ->label('ID Entidad')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('descripcion_detallada')
    ->label('Detalles')
    ->limit(40) // Muestra un extracto
    ->tooltip('Ver detalle completo en la página de vista') // Indica que hay más
    ->formatStateUsing(function (?string $state, Model $record) { // $record es VBitacorasDetalle
        if (!$state) return '-';
        $data = json_decode($state, true); // El modelo ya debería castearlo, pero por si acaso
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            if ($record->accion === 'ACTUALIZAR' && isset($data['antes']) && isset($data['despues'])) {
                // Podríamos intentar mostrar un resumen de los campos cambiados si quisiéramos,
                // pero para la tabla, un simple "Ver detalle" es suficiente.
                return 'Cambios registrados (ver detalle)';
            } elseif (isset($data['nombre'])) { // Para CREAR o ELIMINAR donde hay un 'nombre'
                return Str::limit("Nombre: " . $data['nombre'], 35);
            } elseif (isset($data['codigo_promocion'])) { // Para Promociones
                 return Str::limit("Código: " . $data['codigo_promocion'], 35);
            } elseif (isset($data['nombre_metodo'])) { // Para MetodosPago
                 return Str::limit("Método: " . $data['nombre_metodo'], 35);
            }
            // Un fallback más genérico para otros tipos de JSON
            $keys = array_keys($data);
            return Str::limit(implode(', ', array_slice($keys, 0, 2)) . '...', 35);
        }
        return Str::limit($state, 40); // Si no es JSON válido o es una cadena simple
    })
    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email_usuario') // De la vista
                    ->label('Email Usuario')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('accion')
                    ->options(fn () => Bitacora::query()->distinct()->pluck('accion', 'accion')->all())
                    ->label('Acción'),
                Tables\Filters\SelectFilter::make('referencia_entidad')
                    ->options(fn () => Bitacora::query()->whereNotNull('referencia_entidad')->distinct()->pluck('referencia_entidad', 'referencia_entidad')->all())
                    ->label('Entidad'),
                // Podrías añadir un filtro de fecha aquí
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Para ver detalles (usará ViewBitacora.php)
                // No incluimos EditAction ni DeleteAction
            ])
            ->bulkActions([
                // No incluimos DeleteBulkAction
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('fecha_evento', 'desc'); // Ordenar por fecha de evento descendente por defecto
    }

    /**
     * Deshabilita la creación de registros de bitácora desde Filament.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Deshabilita la edición de registros individuales.
     * Para la ViewAction, esto se maneja en la página ViewBitacora.
     */
    // public static function canEdit(Model $record): bool
    // {
    //     return false;
    // }

    /**
     * Deshabilita la eliminación de registros individuales.
     */
    // public static function canDelete(Model $record): bool
    // {
    //     return false;
    // }

    /**
     * Deshabilita la eliminación masiva.
     */
    // public static function canDeleteAny(): bool
    // {
    //     return false;
    // }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBitacoras::route('/'),
            // No 'create' ni 'edit'
            'view' => Pages\ViewBitacora::route('/{record}'), // Página para ver el detalle
        ];
    }
}