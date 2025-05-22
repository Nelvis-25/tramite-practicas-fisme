<?php

namespace App\Filament\Resources\InformeDePracticaResource\Pages;

use App\Filament\Resources\InformeDePracticaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInformeDePracticas extends ListRecords
{
    protected static string $resource = InformeDePracticaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
