<?php

namespace App\Filament\Resources\PlanDePracticaResource\Pages;

use App\Filament\Resources\PlanDePracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanDePracticas extends ListRecords
{
    protected static string $resource = PlanDePracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
