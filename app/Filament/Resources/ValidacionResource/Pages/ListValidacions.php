<?php

namespace App\Filament\Resources\ValidacionResource\Pages;

use App\Filament\Resources\ValidacionResource;
use App\Models\Requisito;
use App\Models\Solicitud;
use App\Models\Validacion;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;

class ListValidacions extends ListRecords
{
    protected static string $resource = ValidacionResource::class;

    protected function getTableQuery(): Builder
    {
        $solicitud_id = request('solicitud_id');
        
        if (!$solicitud_id) {
            return Validacion::whereNull('id');
        }
    
        // Verificar y crear validaciones si no existen
        $solicitud = Solicitud::with('validaciones')->find($solicitud_id);
        if ($solicitud && $solicitud->validaciones->isEmpty()) {
            $requisitos = Requisito::all();
            foreach ($requisitos as $requisito) {
                Validacion::firstOrCreate([
                    'solicitud_id' => $solicitud->id,
                    'requisito_id' => $requisito->id
                ], [
                    'entregado' => false
                ]);
            }
        }
    
        return Validacion::with(['requisito', 'solicitud'])
            ->where('solicitud_id', $solicitud_id);
    }

    protected function mutateQuery(Builder $query): Builder
    {
        $solicitud_id = request('solicitud_id');

        if ($solicitud_id) {
            $solicitud = Solicitud::with('validaciones')->find($solicitud_id);

            if ($solicitud && $solicitud->validaciones->isEmpty()) {
                $requisitos = Requisito::all();
                
                $validaciones = $requisitos->map(function ($requisito) use ($solicitud) {
                    return [
                        'solicitud_id' => $solicitud->id,
                        'requisito_id' => $requisito->id,
                        'entregado' => false,
                    ];
                });

                Validacion::insert($validaciones->toArray());
                
                // Refrescar la consulta después de insertar
                return Validacion::query()
                    ->with(['requisito', 'solicitud'])
                    ->where('solicitud_id', $solicitud_id);
            }
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('validarCompletado')
                ->label('Marcar todo como validado')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->action(function () {
                    $solicitud_id = request('solicitud_id');
                    Validacion::where('solicitud_id', $solicitud_id)
                        ->update(['entregado' => true]);
                    
                    Notification::make()
                        ->title('Validaciones completadas')
                        ->body('Todos los requisitos fueron marcados como validados')
                        ->success()
                        ->send();
                        
                    $this->refreshTable(); // Actualizar la tabla después de la acción
                })
                ->requiresConfirmation(),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array 
    {
        return [5, 10, 25, 50, 'all' => 'Todos'];
    }

    protected function configureTable(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('requisito.nombre')
                    ->label('Requisito'),
                Tables\Columns\CheckboxColumn::make('entregado')
                    ->label('Validado')
                    ->disabled(fn ($record) => $record->entregado)
            ])
            ->deferLoading()
            ->recordUrl(null);
    }
}