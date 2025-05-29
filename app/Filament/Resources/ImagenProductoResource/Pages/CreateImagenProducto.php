<?php

namespace App\Filament\Resources\ImagenProductoResource\Pages;

use App\Filament\Resources\ImagenProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateImagenProducto extends CreateRecord
{
    protected static string $resource = ImagenProductoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Imagen de producto añadida';
    }

    // Opcional: Mutar datos antes de crear.
    // Por ejemplo, si necesitas procesar algo antes de que el FileUpload lo haga.
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // $data['url_imagen'] = ...;
    //     return $data;
    // }
}
