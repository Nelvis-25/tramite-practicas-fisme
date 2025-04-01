<?php

namespace App\Filament\Resources\PersonalUniversitarioResource\Pages;

use App\Filament\Resources\PersonalUniversitarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPersonalUniversitarios extends ListRecords
{
    protected static string $resource = PersonalUniversitarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
