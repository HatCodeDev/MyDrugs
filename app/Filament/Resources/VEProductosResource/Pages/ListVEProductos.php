<?php

namespace App\Filament\Resources\VEProductosResource\Pages;

use App\Filament\Resources\VEProductosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVEProductos extends ListRecords
{
    protected static string $resource = VEProductosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
