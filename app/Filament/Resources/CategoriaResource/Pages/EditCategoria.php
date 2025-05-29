<?php

namespace App\Filament\Resources\CategoriaResource\Pages;

use App\Filament\Resources\CategoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoria extends EditRecord
{
    protected static string $resource = CategoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(), // Para ver el registro desde la página de edición
            // Actions\ForceDeleteAction::make(), // Si usas SoftDeletes
            // Actions\RestoreAction::make(), // Si usas SoftDeletes
        ];
    }

    // Redirigir después de guardar
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Mensaje de notificación después de guardar
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Categoría actualizada';
    }
}
