<?php

namespace App\Filament\Resources\EvaluacionInformeResource\Pages;

use App\Filament\Resources\EvaluacionInformeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvaluacionInformes extends ListRecords
{
    protected static string $resource = EvaluacionInformeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
