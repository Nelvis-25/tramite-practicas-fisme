<?php

namespace App\Filament\Resources\PracticaResource\Pages;

use App\Filament\Resources\PracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePractica extends CreateRecord
{
    protected static string $resource = PracticaResource::class;
        protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
