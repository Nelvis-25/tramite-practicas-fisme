<?php

namespace App\Filament\Resources\InformePracticaResource\Pages;

use App\Filament\Resources\InformePracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInformePractica extends EditRecord
{
    protected static string $resource = InformePracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
