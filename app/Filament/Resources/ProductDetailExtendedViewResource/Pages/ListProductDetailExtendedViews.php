<?php

namespace App\Filament\Resources\ProductDetailExtendedViewResource\Pages;

use App\Filament\Resources\ProductDetailExtendedViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductDetailExtendedViews extends ListRecords
{
    protected static string $resource = ProductDetailExtendedViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
