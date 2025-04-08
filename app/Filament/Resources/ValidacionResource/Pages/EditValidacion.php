<?php

namespace App\Filament\Resources\ValidacionResource\Pages;

use App\Filament\Resources\ValidacionResource;
use App\Models\Solicitud;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditValidacion extends EditRecord
{
    protected static string $resource = ValidacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('validarTodos')
                ->label('Validar todos')
                ->color('success')
                ->icon('heroicon-o-check')
                ->action(function () {
                    // Marcar todos los requisitos como validados
                    $this->record->solicitud->validaciones()->update(['entregado' => true]);
                    $this->refreshFormData(['validaciones']);
                }),
                
            Actions\Action::make('guardarTodo')
                ->label('Guardar todo')
                ->color('primary')
                ->icon('heroicon-o-document-check')
                ->action(function () {
                    $this->save();
                }),
                
            Actions\DeleteAction::make()
                ->hidden(), // Ocultamos delete ya que no aplica
        ];
    }

    protected function afterSave(): void
    {
        $solicitud = $this->record->solicitud;
        
        // Verificar si todos los requisitos están validados
        $todosValidados = $solicitud->validaciones()
            ->where('entregado', false)
            ->doesntExist();

        // Actualizar estado de la solicitud
        $solicitud->update([
            'estado' => $todosValidados ? 'Validado' : 'Pendiente'
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return \App\Filament\Resources\SolicitudResource::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $solicitud_id = request('solicitud_id');
        
        if ($solicitud_id) {
            $data['validaciones'] = $this->record->solicitud->validaciones;
        }
        
        return $data;
    }
}