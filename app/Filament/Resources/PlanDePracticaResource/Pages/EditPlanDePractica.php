<?php

namespace App\Filament\Resources\PlanDePracticaResource\Pages;

use App\Filament\Resources\PlanDePracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanDePractica extends EditRecord
{
    protected static string $resource = PlanDePracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
