<?php

namespace App\Filament\Resources\ValidacionResource\Pages;

use App\Filament\Resources\ValidacionResource;
use App\Models\Requisito;
use App\Models\Solicitud;
use App\Models\Validacion;
use Closure;
use Filament\Actions;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListValidacions extends ListRecords
{
    protected static string $resource = ValidacionResource::class;

    
    protected function mutateQuery(Builder $query): Builder
    
    {
        $solicitud_id = request('solicitud_id');

        if ($solicitud_id) {
            $solicitud = Solicitud::with('validaciones')->find($solicitud_id);
    
            // Verifica si la solicitud existe
            if ($solicitud) {
                // Si NO tiene validaciones, las creamos
                if ($solicitud->validaciones->isEmpty()) {
                    $requisitos = Requisito::all();
                    
                    foreach ($requisitos as $requisito) {
                        Validacion::create([
                            'solicitud_id' => $solicitud->id,
                            'requisito_id' => $requisito->id,
                            'entregado' => false, // Asegúrate de que esté en fillable
                        ]);
                    }
                }
            }
        }
    
        return $query->where('solicitud_id', $solicitud_id);
        
    }

}