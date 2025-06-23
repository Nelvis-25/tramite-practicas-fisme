<?php

namespace App\Filament\Resources\EvaluacionPlanDePracticaResource\Pages;

use App\Filament\Resources\EvaluacionPlanDePracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvaluacionPlanDePractica extends CreateRecord
{
    protected static string $resource = EvaluacionPlanDePracticaResource::class;

        protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
