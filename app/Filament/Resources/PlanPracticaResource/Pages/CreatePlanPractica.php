<?php

namespace App\Filament\Resources\PlanPracticaResource\Pages;

use App\Filament\Resources\PlanPracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlanPractica extends CreateRecord
{
    protected static string $resource = PlanPracticaResource::class;
        protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
