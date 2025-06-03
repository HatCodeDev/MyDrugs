<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
// Ya no se necesitan los uses de Builder ni de VProductoDetalle aquí,
// a menos que los uses para otras acciones o propiedades específicas de la página.

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    /**
     * Define las acciones del encabezado de la página de listado.
     *
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
