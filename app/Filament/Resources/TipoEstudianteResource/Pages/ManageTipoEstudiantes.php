<?php

namespace App\Filament\Resources\TipoEstudianteResource\Pages;

use App\Filament\Resources\TipoEstudianteResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTipoEstudiantes extends ManageRecords
{
    protected static string $resource = TipoEstudianteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
