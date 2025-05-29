<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImagenProductoResource\Pages;
use App\Filament\Resources\ImagenProductoResource\RelationManagers;
use App\Models\ImagenProducto; // Asegúrate que esta ruta al modelo sea correcta
use App\Models\Producto;       // Necesario para el Select de productos
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage; // Para manejar la eliminación de archivos

class ImagenProductoResource extends Resource
{
    protected static ?string $model = ImagenProducto::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo'; // Icono para imágenes
    protected static ?string $modelLabel = 'Imagen de Producto';
    protected static ?string $pluralModelLabel = 'Imágenes de Productos';
    protected static ?string $recordTitleAttribute = 'alt_text'; // O 'id' si prefieres


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('producto_id')
                    ->label('Producto Asociado')
                    ->relationship(name: 'producto', titleAttribute: 'nombre') // Asume que el modelo Producto tiene un atributo 'nombre'
                    ->searchable()
                    ->preload() // Carga las opciones al inicio
                    ->required()
                    ->placeholder('Selecciona el producto al que pertenece esta imagen')
                    ->createOptionForm([ // Permite crear un producto desde aquí (opcional)
                        // Forms\Components\TextInput::make('nombre')->required()->maxLength(255),
                        // ... otros campos del producto si quieres crearlo rápido
                    ])
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('url_imagen')
                    ->label('Archivo de Imagen')
                    ->image() // Para validar que sea una imagen y mostrar previsualización
                    ->imageEditor() // Habilita un editor básico de imágenes (recortar, rotar)
                    ->directory('imagenes_productos') // Directorio donde se guardarán en tu disk 'public'
                    ->preserveFilenames() // Conserva el nombre original del archivo
                    ->required()
                    ->maxSize(2048) // Tamaño máximo en KB (ej: 2MB)
                    ->helperText('Sube la imagen del producto. Tamaño máximo: 2MB.')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('alt_text')
                    ->label('Texto Alternativo (Alt Text)')
                    ->nullable()
                    ->maxLength(255)
                    ->placeholder('Descripción breve de la imagen para accesibilidad y SEO')
                    ->helperText('Importante para SEO y accesibilidad.')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('orden')
                    ->label('Orden de Visualización')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('Un número menor se mostrará primero. 0 es el valor por defecto.')
                    ->required(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\ImageColumn::make('url_imagen')
                    ->label('Imagen')
                    ->disk('public') // Asegúrate que coincida con tu config/filesystems.php y el FileUpload
                    ->height(60) // Altura de la miniatura en la tabla
                    ->circular(), // Muestra la imagen como un círculo
                Tables\Columns\TextColumn::make('producto.nombre') // Muestra el nombre del producto relacionado
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->url(fn (ImagenProducto $record): ?string => $record->producto ? ProductoResource::getUrl('edit', ['record' => $record->producto_id]) : null) // Enlace al producto
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('alt_text')
                    ->label('Texto Alternativo')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (ImagenProducto $record): ?string => $record->alt_text)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('orden')
                    ->label('Orden')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subida el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('producto_id')
                    ->label('Filtrar por Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    // Asegúrate de eliminar el archivo físico al borrar el registro
                    ->after(function (ImagenProducto $record) {
                        if ($record->url_imagen) {
                            Storage::disk('public')->delete($record->url_imagen);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function (\Illuminate\Database\Eloquent\Collection $records) {
                            foreach ($records as $record) {
                                if ($record->url_imagen) {
                                    Storage::disk('public')->delete($record->url_imagen);
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListImagenProductos::route('/'), // Corregido según tu estructura
            'create' => Pages\CreateImagenProducto::route('/create'),
            'edit' => Pages\EditImagenProducto::route('/{record}/edit'),
            // 'view' => Pages\ViewImagenProducto::route('/{record}'),
        ];
    }
}
