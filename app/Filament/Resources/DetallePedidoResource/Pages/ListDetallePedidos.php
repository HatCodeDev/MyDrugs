<?php

namespace App\Filament\Resources\DetallePedidoResource\Pages;

use App\Filament\Resources\DetallePedidoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetallePedidos extends ListRecords
{
    protected static string $resource = DetallePedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
