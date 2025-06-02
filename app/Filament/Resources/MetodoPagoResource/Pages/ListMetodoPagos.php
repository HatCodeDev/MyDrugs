<?php

namespace App\Filament\Resources\MetodoPagoResource\Pages;

use App\Filament\Resources\MetodoPagoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMetodosPago extends ListRecords
{
    protected static string $resource = MetodoPagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // Botón para ir a la página de creación
        ];
    }
}