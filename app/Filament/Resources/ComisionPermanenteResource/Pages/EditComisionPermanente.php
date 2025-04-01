<?php

namespace App\Filament\Resources\ComisionPermanenteResource\Pages;

use App\Filament\Resources\ComisionPermanenteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComisionPermanente extends EditRecord
{
    protected static string $resource = ComisionPermanenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
