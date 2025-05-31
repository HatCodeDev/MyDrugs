<?php

namespace App\Filament\Resources\ProductDetailExtendedViewResource\Pages;

use App\Filament\Resources\ProductDetailExtendedViewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductDetailExtendedView extends EditRecord
{
    protected static string $resource = ProductDetailExtendedViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
