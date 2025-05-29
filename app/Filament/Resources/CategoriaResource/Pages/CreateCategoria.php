<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoria extends CreateRecord
{
    protected static string $resource = CategoriaResource::class;

    // Redirigir después de crear
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Cambiar el título de la página si es necesario
    // protected function getTitle(): string
    // {
    //     return 'Crear Nueva Categoría';
    // }

    // Mensaje de notificación después de crear
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Categoría creada';
    }
}

