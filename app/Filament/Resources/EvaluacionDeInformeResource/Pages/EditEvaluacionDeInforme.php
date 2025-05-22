<?php

namespace App\Filament\Resources\EvaluacionDeInformeResource\Pages;

use App\Filament\Resources\EvaluacionDeInformeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvaluacionDeInforme extends EditRecord
{
    protected static string $resource = EvaluacionDeInformeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
