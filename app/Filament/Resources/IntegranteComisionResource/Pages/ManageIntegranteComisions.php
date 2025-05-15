<?php

namespace App\Filament\Resources\IntegranteComisionResource\Pages;

use App\Filament\Resources\IntegranteComisionResource;
use App\Models\IntegranteComision;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class ManageIntegranteComisions extends ManageRecords
{
    protected static string $resource = IntegranteComisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->using(function (array $data) {
                    foreach ($data['integrantes'] as $integrante) {
                        // Validar si ya existe el docente en la misma comisión
                        $yaExisteDocente = IntegranteComision::where('comision_permanente_id', $data['comision_permanente_id'])
                            ->where('docente_id', $integrante['docente_id'])
                            ->exists();

                        // Validar si ya existe el cargo en la misma comisión
                        $yaExisteCargo = IntegranteComision::where('comision_permanente_id', $data['comision_permanente_id'])
                            ->where('cargo', $integrante['cargo'])
                            ->exists();

                        if ($yaExisteDocente || $yaExisteCargo) {
                            $mensaje = '';

                            if ($yaExisteDocente) {
                                $mensaje .= 'Este docente ya ha sido asignado en esta comisión.';
                            }

                            if ($yaExisteCargo) {
                                $mensaje .= 'Este cargo ya ha sido asignado en esta comisión.';
                            }

                            // Notificación visual en el formulario
                            Notification::make()
                                ->title('No se pudo guardar un registro')
                                ->body($mensaje)
                                ->danger()
                                ->send();

                            // Lanza error de validación sin cerrar el formulario
                            throw ValidationException::withMessages([
                                'integrantes' => $mensaje,
                            ]);
                        }

                        // Crear registro si pasa la validación
                        IntegranteComision::create([
                            'comision_permanente_id' => $data['comision_permanente_id'],
                            'docente_id' => $integrante['docente_id'],
                            'cargo' => $integrante['cargo'],
                        ]);
                    }

                    // Retornar uno de los registros creados
                    return IntegranteComision::latest()->first();
                }),
        ];
    }
}
