<?php

namespace App\Filament\Resources\PromocionResource\Pages;

use App\Filament\Resources\PromocionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromociones extends ListRecords
{
    protected static string $resource = PromocionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // Botón para ir a la página de creación
        ];
    }
}