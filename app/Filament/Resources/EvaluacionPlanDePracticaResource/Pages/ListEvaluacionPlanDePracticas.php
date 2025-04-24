<?php

namespace App\Filament\Resources\EvaluacionPlanDePracticaResource\Pages;

use App\Filament\Resources\EvaluacionPlanDePracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvaluacionPlanDePracticas extends ListRecords
{
    protected static string $resource = EvaluacionPlanDePracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
