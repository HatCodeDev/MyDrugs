<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetodoPagoResource\Pages;
use App\Models\MetodoPago;
use App\Models\VMetodosPagoDetalle; // Modelo para la vista
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage; // Si manejas subida de logos
use Illuminate\Support\Str; // Importar Str para la verificación de URL
use Illuminate\Support\Facades\Log; // Importar Log para los mensajes

class MetodoPagoResource extends Resource
{
    protected static ?string $model = MetodoPago::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card'; // Ícono para métodos de pago
    protected static ?string $modelLabel = 'Método de Pago';
    protected static ?string $pluralModelLabel = 'Métodos de Pago';
    protected static ?string $recordTitleAttribute = 'nombre_metodo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_metodo')
                    ->required()
                    ->maxLength(100)
                    ->label('Nombre del Método'),
                Forms\Components\Textarea::make('descripcion_instrucciones')
                    ->nullable()
                    ->columnSpanFull()
                    ->label('Descripción / Instrucciones'),
                Forms\Components\TextInput::make('comision_asociada_porcentaje')
                    ->numeric()
                    ->required()
                    ->default(0.00)
                    ->prefix('%')
                    ->minValue(0)
                    ->maxValue(100) // Asumiendo que es un porcentaje
                    ->step(0.01) // Permite dos decimales en la entrada del formulario
                    ->label('Comisión Asociada (%)'),
                Forms\Components\Toggle::make('activo')
                    ->required()
                    ->default(true)
                    ->label('Activo'),
                Forms\Components\FileUpload::make('logo_url') // Cambiado a FileUpload
                    ->label('Logo del Método de Pago')
                    ->disk('public')
                    ->directory('metodos_pago_logos')
                    ->image()
                    ->imageEditor() // Opcional
                    ->nullable()
                    ->helperText('Sube un logo. Si no se sube uno nuevo al editar, se conservará el existente.'),
                // Si prefieres una URL externa en lugar de subir el archivo:
                // Forms\Components\TextInput::make('logo_url')
                // ->nullable()
                // ->maxLength(255)
                // ->url()
                // ->label('URL del Logo (Opcional)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(VMetodosPagoDetalle::query()) // Usar la vista para el listado
            ->columns([
                Tables\Columns\TextColumn::make('metodo_pago_id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('logo_url') // Mostrar el logo
                    ->label('Logo')
                    ->disk('public') // Si los logos se guardan localmente
                    ->height(40)
                    ->defaultImageUrl(url('/images/default_payment_logo.png')) // Opcional: imagen por defecto
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('nombre_metodo')
                    ->label('Nombre del Método')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comision_asociada_porcentaje')
                    ->label('Comisión (%)')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 2) // <--- CORRECCIÓN AQUÍ
                    ->sortable(),
                Tables\Columns\IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion_instrucciones')
                    ->label('Descripción')
                    ->limit(40)
                    ->tooltip('Ver descripción completa') //  Mejorado: tooltip(fn (Model $record) => $record->descripcion_instrucciones) para ver todo
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
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
                    ->action(function (Model $record) { // $record es VMetodosPagoDetalle
                        $dbSuccess = false;
                        $fileHandledSuccessfully = false;
                        $filePath = $record->logo_url; // Asumiendo que logo_url es una ruta de archivo local
                        $finalMessage = 'Error desconocido durante la eliminación.';
                        $dbEditorConnection = DB::connection('mysql_editor');
                        try {
                            $dbEditorConnection->statement(
                                "CALL sp_eliminar_metodo_pago(?, @success, @message)",
                                [$record->metodo_pago_id]
                            );
                            $result = $dbEditorConnection->selectOne("SELECT @success AS success, @message AS message");

                            if ($result && $result->success) {
                                $dbSuccess = true;
                                $finalMessage = $result->message ?: 'Método de pago eliminado de la base de datos.';

                                if (!empty($filePath)) { // Solo intentar borrar si hay una ruta
                                    try {
                                        if (Str::startsWith($filePath, ['http://', 'https://'])) {
                                            // Es una URL externa, no intentar borrar localmente
                                            $fileHandledSuccessfully = true;
                                        } elseif (Storage::disk('public')->exists($filePath)) {
                                            if (Storage::disk('public')->delete($filePath)) {
                                                $fileHandledSuccessfully = true;
                                            } else {
                                                Log::warning("METODO_PAGO_DELETE: No se pudo eliminar el archivo de logo físico '{$filePath}' para metodo_pago_id {$record->metodo_pago_id}.");
                                                $finalMessage .= ' Sin embargo, el archivo de logo físico no pudo ser eliminado.';
                                            }
                                        } else {
                                            Log::info("METODO_PAGO_DELETE: El archivo de logo físico '{$filePath}' no existía para metodo_pago_id {$record->metodo_pago_id}. Se considera manejado.");
                                            $fileHandledSuccessfully = true;
                                        }
                                    } catch (\Exception $fileException) {
                                        Log::error("METODO_PAGO_DELETE: Excepción al eliminar archivo de logo '{$filePath}' para metodo_pago_id {$record->metodo_pago_id}: " . $fileException->getMessage());
                                        $finalMessage .= ' Ocurrió una excepción al intentar eliminar el archivo de logo.';
                                    }
                                } else {
                                    $fileHandledSuccessfully = true; // No hay archivo que borrar
                                }
                            } else {
                                $finalMessage = $result->message ?: 'No se pudo eliminar el método de pago (según SP).';
                            }
                        } catch (\Exception $e) {
                            report($e); // Usa la función helper report() de Laravel para registrar la excepción
                            Log::error("METODO_PAGO_DELETE: Error inesperado durante la operación de BD para metodo_pago_id {$record->metodo_pago_id}: " . $e->getMessage());
                            $finalMessage = 'Error inesperado durante la operación de base de datos.';
                        }

                        if ($dbSuccess && $fileHandledSuccessfully) {
                            Notification::make()->title('¡Eliminación Completada!')->body($finalMessage)->success()->send();
                        } elseif ($dbSuccess && !$fileHandledSuccessfully) {
                            Notification::make()->title('Eliminación Parcial')->body($finalMessage)->warning()->send();
                        } else {
                            Notification::make()->title('Error en la Eliminación')->body($finalMessage)->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Considerar personalizar si necesitas manejo de SPs y logos en masa.
                ]),
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
            'index' => Pages\ListMetodosPago::route('/'),
            'create' => Pages\CreateMetodoPago::route('/create'),
            'edit' => Pages\EditMetodoPago::route('/{record}/edit'),
        ];
    }
}