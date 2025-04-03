<?php

namespace App\Filament\Resources\ComisionPermanenteResource\Pages;

use App\Filament\Resources\ComisionPermanenteResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageComisionPermanentes extends ManageRecords
{
    protected static string $resource = ComisionPermanenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
