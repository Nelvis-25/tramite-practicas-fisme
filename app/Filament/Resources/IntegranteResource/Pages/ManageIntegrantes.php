<?php

namespace App\Filament\Resources\IntegranteResource\Pages;

use App\Filament\Resources\IntegranteResource;
use App\Models\Integrante;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Form;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
class ManageIntegrantes extends ManageRecords
{
    protected static string $resource = IntegranteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->using(function (array $data) {
                foreach ($data['integrantes'] as $integrante) {
                    // Validar si ya existe el docente en la misma jurado informe
                    $yaExisteDocente = Integrante::where('jurado_informe_id', $data['jurado_informe_id'])
                        ->where('docente_id', $integrante['docente_id'])
                        ->exists();

                    // Validar si ya existe el cargo en la misma jurado informe
                    $yaExisteCargo = Integrante::where('jurado_informe_id', $data['jurado_informe_id'])
                        ->where('cargo', $integrante['cargo'])
                        ->exists();

                    if ($yaExisteDocente || $yaExisteCargo) {
                        $mensaje = '';

                        if ($yaExisteDocente) {
                            $mensaje .= 'Este docente ya ha sido asignado en esta jurado informe.';
                        }

                        if ($yaExisteCargo) {
                            $mensaje .= 'Este cargo ya ha sido asignado en esta jurado informe.';
                        }

                        Notification::make()
                            ->title('No se pudo guardar un registro')
                            ->body($mensaje)
                            ->danger()
                            ->send();

                        throw ValidationException::withMessages([
                            'integrantes'=> $mensaje,
                        ]);
                    }
                    Integrante::create([

                        'jurado_informe_id' => $data['jurado_informe_id'],
                        'docente_id' => $integrante['docente_id'],
                        'cargo' => $integrante['cargo'],
                    ]);
                }
                return Integrante::latest()->first();
            }),
        ];
    }
}
