<?php

namespace App\Filament\Resources\ProductDetailExtendedViewResource\Pages;

use App\Filament\Resources\ProductDetailExtendedViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductDetailExtendedView extends ViewRecord
{
    protected static string $resource = ProductDetailExtendedViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
