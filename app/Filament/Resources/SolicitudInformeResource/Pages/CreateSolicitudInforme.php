<?php

namespace App\Filament\Resources\SolicitudInformeResource\Pages;

use App\Filament\Resources\SolicitudInformeResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSolicitudInforme extends CreateRecord
{
    protected static string $resource = SolicitudInformeResource::class;
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
                        ->title('Nueva Solicitud de Informe registrada')
                        ->body('Tienes una solicitud de informe pendiente de validación.<br><a href="' . route('filament.admin.resources.solicitud-informes.index') . '" style="color:#3b82f6;text-decoration:underline;">Ver solicitudes</a>')
                        ->success()
                        ->sendToDatabase($usuario);
                }
            }
}
