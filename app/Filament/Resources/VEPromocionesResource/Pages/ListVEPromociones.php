<?php

namespace App\Filament\Resources\VEPromocionesResource\Pages;

use App\Filament\Resources\VEPromocionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVEPromociones extends ListRecords
{
    protected static string $resource = VEPromocionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
