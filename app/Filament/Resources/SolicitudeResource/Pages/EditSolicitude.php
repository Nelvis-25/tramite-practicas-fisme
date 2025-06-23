<?php

namespace App\Filament\Resources\SolicitudeResource\Pages;

use App\Filament\Resources\SolicitudeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitude extends EditRecord
{
    protected static string $resource = SolicitudeResource::class;

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
