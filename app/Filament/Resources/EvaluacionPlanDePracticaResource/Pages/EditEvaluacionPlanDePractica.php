<?php

namespace App\Filament\Resources\EvaluacionPlanDePracticaResource\Pages;

use App\Filament\Resources\EvaluacionPlanDePracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvaluacionPlanDePractica extends EditRecord
{
    protected static string $resource = EvaluacionPlanDePracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    //protected function afterSave(): void
    //{
        // Actualizamos el estado del Plan de Prácticas
       // $this->record->planPractica->actualizarEstadoPorEvaluaciones();
   // }
}
