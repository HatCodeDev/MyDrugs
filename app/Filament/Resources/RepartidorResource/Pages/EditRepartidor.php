<?php

namespace App\Filament\Resources\RepartidorResource\Pages;

use App\Filament\Resources\RepartidorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepartidor extends EditRecord
{
    protected static string $resource = RepartidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Datos del repartidor actualizados';
    }
}
