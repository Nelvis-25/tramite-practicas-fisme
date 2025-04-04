<?php

namespace App\Filament\Resources\ValidacionResource\Pages;

use App\Filament\Resources\ValidacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditValidacion extends EditRecord
{
    protected static string $resource = ValidacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
{
    // Obtiene la solicitud relacionada a esta validación
    $solicitud = $this->record->solicitud;
    
    // Verifica si TODOS los requisitos están entregados (entregado = true)
    $todosEntregados = $solicitud->validaciones()
        ->where('entregado', false)
        ->doesntExist();

    // Actualiza el estado de la solicitud
    $solicitud->update([
        'estado' => $todosEntregados ? 'Validado' : 'Rechazado'
    ]);
}

protected function getRedirectUrl(): string
{
    // Redirige al listado de solicitudes después de guardar
    return \App\Filament\Resources\SolicitudResource::getUrl('index');
}
}
