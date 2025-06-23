<?php

namespace App\Filament\Resources\EvaluacionDeInformeResource\Pages;

use App\Filament\Resources\EvaluacionDeInformeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEvaluacionDeInforme extends CreateRecord
{
    protected static string $resource = EvaluacionDeInformeResource::class;
        protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
