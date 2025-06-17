<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ObservacionEvaluacionPlan;
class EvaluacionPlanDePractica extends Model
{
    use HasFactory;
    protected $table = 'evaluacion_plan_de_practicas';

    protected $fillable = [
       
        'plan_practica_id',
        'docente_id',
        'integrante_comision_id',
        'estado',
        'observacion',
        'activo',
    ];
    protected $casts = [
        'activo' => 'boolean',
    ];

    public function planPractica(): BelongsTo
    {
        return $this->belongsTo(PlanPractica::class);
    }
    public function integranteComision()
    {
        return $this->belongsTo(IntegranteComision::class);
    }

    // Acceder al docente relacionado a través de Integrante_Comision
    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }
     public function observaciones()
        {
         return $this->hasMany(ObservacionEvaluacionPlan::class, 'evaluacion_plan_id');
         }  

 
    protected static function booted()
    {
        static::updated(function ($evaluacion) {
            // Solo si el estado ya no está en Pendiente
            if (!in_array($evaluacion->estado, ['Aprobado', 'Observado', 'Desaprobado'])) {
                return;
            }

            $evaluacion->actualizarEstadoPlanPadre();
        });
    }

    public function actualizarEstadoPlanPadre()
    {
        $plan = $this->planPractica;

        // Obtener evaluaciones que ya fueron completadas (no pendientes)
        $evaluacionesCompletadas = $plan->evaluaciones()
            ->whereIn('estado', ['Aprobado', 'Observado', 'Desaprobado'])
            ->get();

        if ($evaluacionesCompletadas->count() < 3) {
            return; 
        }

        // Determinar el nuevo estado del plan
        if ($evaluacionesCompletadas->contains('estado', 'Desaprobado')) {
            $nuevoEstado = 'Desaprobado';
        } elseif ($evaluacionesCompletadas->contains('estado', 'Observado')) {
            $nuevoEstado = 'Observado';
        } else {
            $nuevoEstado = 'Aprobado';
        }
        
        $plan->updateQuietly([
            'estado' => $nuevoEstado,
            'observaciones' => 'Sustentado',
        ]);

        $plan->evaluaciones()
            ->where('estado', 'Pendiente')
            ->delete();

        if ($nuevoEstado === 'Desaprobado') {
        $plan->solicitude->updateQuietly(['activo' => false]);
          }
    
            if ($nuevoEstado === 'Aprobado') {
                $existePractica = \App\Models\Practica::where('plan_practica_id', $plan->id)->first();
            
                if (!$existePractica) {
                    \App\Models\Practica::create([
                        'estudiante_id' => $plan->solicitude->estudiante_id,
                        'docente_id' => $plan->solicitude->asesor_id,
                        'solicitude_id' => $plan->solicitude_id,
                        'plan_practica_id' => $plan->id,
                        'estado' => 'En Desarrollo',
                    ]);
                }
            }
    
    
    
    }




}