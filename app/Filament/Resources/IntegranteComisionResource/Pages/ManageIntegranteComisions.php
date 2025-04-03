<?php

namespace App\Filament\Resources\IntegranteComisionResource\Pages;

use App\Filament\Resources\IntegranteComisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageIntegranteComisions extends ManageRecords
{
    protected static string $resource = IntegranteComisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
