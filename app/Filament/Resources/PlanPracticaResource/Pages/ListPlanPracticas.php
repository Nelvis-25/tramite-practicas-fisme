<?php

namespace App\Filament\Resources\PlanPracticaResource\Pages;

use App\Filament\Resources\PlanPracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanPracticas extends ListRecords
{
    protected static string $resource = PlanPracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
