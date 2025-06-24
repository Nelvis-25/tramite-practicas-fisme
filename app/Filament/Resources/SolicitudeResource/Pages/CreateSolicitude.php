<?php

namespace App\Filament\Resources\SolicitudeResource\Pages;

use App\Filament\Resources\SolicitudeResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSolicitude extends CreateRecord
{
    protected static string $resource = SolicitudeResource::class;
    protected function getRedirectUrl(): string
        {
            return $this->getResource()::getUrl('index');
        }
    protected function afterCreate(): void
    {
        /** @var User $user */
        $usuariosSecretaria = User::role('Secretaria')->get();

        foreach ($usuariosSecretaria as $usuario) {
            Notification::make()
                ->title('Nueva Solicitud registrada')
                ->body('Tienes una solicitud pendiente de validación.')
                 ->success()
                ->sendToDatabase($usuario);
        }
    }
}
