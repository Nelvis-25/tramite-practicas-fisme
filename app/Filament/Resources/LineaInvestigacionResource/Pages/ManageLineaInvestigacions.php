<?php

namespace App\Filament\Resources\LineaInvestigacionResource\Pages;

use App\Filament\Resources\LineaInvestigacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLineaInvestigacions extends ManageRecords
{
    protected static string $resource = LineaInvestigacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
