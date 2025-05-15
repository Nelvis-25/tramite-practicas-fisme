<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionInforme extends Model
{
    use HasFactory;
    protected $table = 'evaluacion_informes';
    protected $fillable = [
        'informe_practica_id',
        'integrante_id',
        'fecha_evaluacion',
        'observacion',
        'estado',
        'activo',
    ];

    public function  informePractica() 
    {
        return $this->belongsTo(InformePractica::class,  'informe_practica_id' );
    }
    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }
    public function integrante()//
    {
        return $this->belongsTo(Integrante::class,   );
    }
    protected static function booted()
    {
        static::updated(function ($evaluacioinforme) {
            // Solo si el estado ya no está en Pendiente
            if (!in_array($evaluacioinforme->estado, ['Aprobado', 'Observado', 'Desaprobado'])) {
                return;
            }

            $evaluacioinforme->actualizarEstadoPlanPadre();
        });
    }

    public function actualizarEstadoPlanPadre()
    {
        $plan = $this->informePractica;

        // Obtener evaluaciones que ya fueron completadas (no pendientes)
        $evaluacionesCompletadas = $plan->evaluaciones()
            ->whereIn('estado', ['Aprobado', 'Observado', 'Desaprobado'])
            ->get();

        if ($evaluacionesCompletadas->count() < 3) {
            return; // Aún no hay 3 evaluaciones => no hacer nada
        }

        // Determinar el nuevo estado del plan
        if ($evaluacionesCompletadas->contains('estado', 'Desaprobado')) {
            $nuevoEstado = 'Desaprobado';
        } elseif ($evaluacionesCompletadas->contains('estado', 'Observado')) {
            $nuevoEstado = 'Observado';
        } else {
            $nuevoEstado = 'Aprobado';
        }

        // ✅ Actualizar el estado del plan
        $plan->updateQuietly(
            [
                'estado' => $nuevoEstado,
                'observaciones' => 'Sustentado',
            ]);

        // ✅ Eliminar automáticamente las evaluaciones pendientes (como la del Accesitario)
        $plan->evaluaciones()
            ->where('estado', 'Pendiente')
            ->delete();
        
        
        if ($plan->solicitudInforme && $plan->solicitudInforme->practica) {
            $plan->solicitudInforme->practica->updateQuietly([
                'estado' => $nuevoEstado
            ]);
             }
        
        }
}
