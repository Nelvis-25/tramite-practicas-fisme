<?php

namespace App\Filament\Resources\PracticaResource\Pages;

use App\Filament\Resources\PracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPractica extends EditRecord
{
    protected static string $resource = PracticaResource::class;

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
