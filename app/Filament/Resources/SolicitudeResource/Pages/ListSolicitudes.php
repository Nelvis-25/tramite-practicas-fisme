<?php

namespace App\Filament\Resources\SolicitudeResource\Pages;

use App\Filament\Resources\SolicitudeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudes extends ListRecords
{
    protected static string $resource = SolicitudeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
