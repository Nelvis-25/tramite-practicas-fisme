<?php

namespace App\Filament\Resources\SolicitudInformeResource\Pages;

use App\Filament\Resources\SolicitudInformeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudInformes extends ListRecords
{
    protected static string $resource = SolicitudInformeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
