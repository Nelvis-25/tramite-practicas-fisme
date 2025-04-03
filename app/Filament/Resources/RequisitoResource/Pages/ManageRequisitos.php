<?php

namespace App\Filament\Resources\RequisitoResource\Pages;

use App\Filament\Resources\RequisitoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageRequisitos extends ManageRecords
{
    protected static string $resource = RequisitoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
