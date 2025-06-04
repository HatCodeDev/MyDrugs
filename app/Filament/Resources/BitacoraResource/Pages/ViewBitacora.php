<?php

namespace App\Filament\Resources\BitacoraResource\Pages;

use App\Filament\Resources\BitacoraResource;
use App\Models\Bitacora; // Para el tipado del registro
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Support\Str;

class ViewBitacora extends ViewRecord
{
    protected static string $resource = BitacoraResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Components\Grid::make(2) // Grid principal de 2 columnas
                    ->schema([
                        Components\Group::make() // Columna Izquierda
                            ->schema([
                                Components\TextEntry::make('id')
                                    ->label('ID Bitácora'),
                                Components\TextEntry::make('accion')
                                    ->label('Acción Realizada')
                                    ->badge()
                                    ->color(fn (string $state): string => match (strtolower($state)) {
                                        'crear', 'insertar', 'nueva_categoria', 'nuevo_producto' => 'success',
                                        'actualizar', 'modificar' => 'primary',
                                        'eliminar', 'borrar' => 'danger',
                                        default => 'gray',
                                    }),
                                Components\TextEntry::make('user.email') // Accede a través de la relación 'user'
                                    ->label('Email del Usuario')
                                    ->placeholder('N/A'),
                                Components\TextEntry::make('referencia_id')
                                    ->label('ID de la Entidad Afectada')
                                    ->placeholder('N/A'),
                                Components\TextEntry::make('created_at')
                                    ->label('Registro Creado en Bitácora')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                        Components\Group::make() // Columna Derecha
                            ->schema([
                                Components\TextEntry::make('fecha_evento')
                                    ->label('Fecha del Evento')
                                    ->dateTime('d/m/Y H:i:s'),
                                Components\TextEntry::make('user.name') // Accede a través de la relación 'user' del modelo Bitacora
                                    ->label('Nombre del Usuario')
                                    ->placeholder('Sistema/Trigger'),
                                Components\TextEntry::make('referencia_entidad')
                                    ->label('Entidad Afectada')
                                    ->placeholder('N/A'),
                                // Placeholder para mantener alineación si es necesario, o añadir otro campo
                                Components\TextEntry::make('updated_at')
                                    ->label('Última Actualización del Registro')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ]),
                Components\Section::make('Descripción Detallada de la Acción')
                    ->schema([
                        // Usando la vista Blade personalizada que funcionó
                        Components\View::make('filament.infolists.components.json-viewer') // Referencia directa a la vista Blade
    ->viewData([
        'state' => $this->record->descripcion_detallada, // Pasamos el estado aquí
        'level' => 0 // Nivel inicial para la recursividad en tu Blade
    ])
    ->columnSpanFull() // Para que ocupe el ancho completo si está en un grid
                    ])
                    ->collapsible()
                    // La lógica para colapsar si no es 'ACTUALIZAR'
                    ->collapsed(fn ($record) => $record instanceof Bitacora && $record->accion !== 'ACTUALIZAR'),
            ]);
    }
}