<?php

namespace App\Filament\Resources\BitacoraResource\Pages;

use App\Filament\Resources\BitacoraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBitacoras extends ListRecords
{
    protected static string $resource = BitacoraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No incluimos Actions\CreateAction::make() porque canCreate() devuelve false
        ];
    }

    // La consulta para la tabla ya está definida en BitacoraResource::table()
    // usando ->query(VBitacorasDetalle::query())
}