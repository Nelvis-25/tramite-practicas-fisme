<?php

namespace App\Filament\Resources\InformeDePracticaResource\Pages;

use App\Filament\Resources\InformeDePracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInformeDePractica extends EditRecord
{
    protected static string $resource = InformeDePracticaResource::class;

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
