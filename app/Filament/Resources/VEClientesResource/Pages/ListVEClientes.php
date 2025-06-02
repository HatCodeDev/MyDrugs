<?php

namespace App\Filament\Resources\VEClientesResource\Pages;

use App\Filament\Resources\VEClientesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVEClientes extends ListRecords
{
    protected static string $resource = VEClientesResource::class;

    protected function getHeaderActions(): array
    {
        return [
           
        ];
    }
}
