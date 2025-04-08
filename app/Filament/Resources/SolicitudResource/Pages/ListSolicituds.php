<?php

namespace App\Filament\Resources\SolicitudResource\Pages;

use App\Filament\Resources\SolicitudResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\MountableAction;

class ListSolicituds extends ListRecords
{
    protected static string $resource = SolicitudResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            TableAction::make('notas')
                ->label('Notas')
                ->icon('heroicon-o-document-text')
                ->mountUsing(fn ($record, MountableAction $action) =>
                    $action->arguments(['solicitudId' => $record->id])
                )
                ->modalHeading('Historial de notas')
                ->modalSubmitAction(false)
                ->form([])
                ->action(fn () => null)
                ->slideOver() // O usa ->modal() si prefieres el modal centrado
                ->livewire('observacion-modal'), // 👈 Tu componente Livewire
        ];
    }
}
