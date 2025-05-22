<?php

namespace App\Filament\Resources\JuradoDeInformeResource\Pages;

use App\Filament\Resources\JuradoDeInformeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJuradoDeInformes extends ManageRecords
{
    protected static string $resource = JuradoDeInformeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
