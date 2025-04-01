<?php

namespace App\Filament\Resources\PersonalUniversitarioResource\Pages;

use App\Filament\Resources\PersonalUniversitarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPersonalUniversitario extends EditRecord
{
    protected static string $resource = PersonalUniversitarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
