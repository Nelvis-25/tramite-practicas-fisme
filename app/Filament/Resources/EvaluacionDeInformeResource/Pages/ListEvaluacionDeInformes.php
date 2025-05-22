<?php

namespace App\Filament\Resources\EvaluacionDeInformeResource\Pages;

use App\Filament\Resources\EvaluacionDeInformeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvaluacionDeInformes extends ListRecords
{
    protected static string $resource = EvaluacionDeInformeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
