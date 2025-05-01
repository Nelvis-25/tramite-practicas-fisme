<?php

namespace App\Filament\Resources\IntegranteResource\Pages;

use App\Filament\Resources\IntegranteResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageIntegrantes extends ManageRecords
{
    protected static string $resource = IntegranteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
