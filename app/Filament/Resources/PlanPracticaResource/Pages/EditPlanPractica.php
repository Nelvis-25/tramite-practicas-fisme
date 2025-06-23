<?php

namespace App\Filament\Resources\PlanPracticaResource\Pages;

use App\Filament\Resources\PlanPracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanPractica extends EditRecord
{
    protected static string $resource = PlanPracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
        protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
