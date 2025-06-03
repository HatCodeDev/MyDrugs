<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User; 
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Support\Facades\DB;      
use Filament\Notifications\Notification; 
use Illuminate\Support\Facades\Log;      
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {

        $name = $data['name'];
        $email = $data['email'];

        // La contraseña ya viene hasheada desde el componente del formulario
        // gracias a ->dehydrateStateUsing(fn ($state) => Hash::make($state))
        // y ->dehydrated(fn ($state) => filled($state))
        $hashedPassword = $data['password'] ?? null; // Debería estar presente porque es required en 'create'

        // $data['roles'] será un array de IDs de roles seleccionados
        $roleIds = $data['roles'] ?? [];
        $roleIdsJson = count($roleIds) > 0 ? json_encode($roleIds) : null;

        try {
            DB::statement(
                "CALL sp_crear_user(?, ?, ?, ?, @success, @message, @user_id)",
                [
                    $name,
                    $email,
                    $hashedPassword,
                    $roleIdsJson
                ]
            );

            $result = DB::selectOne("SELECT @success AS success, @message AS message, @user_id AS user_id");

            if ($result && $result->success) {
                Notification::make()
                    ->title('¡Éxito!')
                    ->body($result->message ?: 'Usuario creado exitosamente.')
                    ->success()
                    ->send();

                $user = User::find($result->user_id);
                if (!$user) {
                    Notification::make()->title('Error de Sincronización')
                        ->body('El usuario se creó en la BD (según SP) pero no se pudo cargar el modelo.')
                        ->danger()->send();
                    $this->halt();
                    return new User(); 
                }
                return $user;
            } else {
                $errorMessage = 'Error desconocido desde el SP.';
                if ($result && isset($result->message)) {
                    $errorMessage = $result->message;
                } elseif (!$result) {
                    $errorMessage = 'El SP no devolvió un resultado (variables @success, @message no recuperadas).';
                }

                Notification::make()->title('Error al Crear Usuario')
                    ->body($errorMessage)
                    ->danger()->send();
                $this->halt();
                return new User();
            }
        } catch (\Exception $e) {
            
            report($e);
            Notification::make()->title('Error Inesperado de Base de Datos')
                ->body('Ocurrió un problema técnico al crear el usuario: ' . $e->getMessage())
                ->danger()->send();
            $this->halt();
            return new User(); 
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Desactivamos la notificación por defecto de Filament
    protected function getCreatedNotificationTitle(): ?string
    {
        return null;
    }
}