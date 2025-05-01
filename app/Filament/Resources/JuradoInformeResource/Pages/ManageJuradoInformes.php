<?php

namespace App\Filament\Resources\JuradoInformeResource\Pages;

use App\Filament\Resources\JuradoInformeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJuradoInformes extends ManageRecords
{
    protected static string $resource = JuradoInformeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
