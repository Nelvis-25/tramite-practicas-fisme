<?php

namespace App\Observers;

use App\Models\PlanPractica;
use App\Models\Practica;

class PlanPracticaObserver
{
    public function updated(PlanPractica $plan)
    {
        // Solo actuar cuando el estado cambia a "Aprobado"
        if ($plan->estado == 'Aprobado' && $plan->isDirty('estado')) {
            $this->crearPractica($plan);
        }
    }

    private function crearPractica(PlanPractica $plan)
    {
        // Evitar duplicados
        if (!$plan->practica()->exists()) {
            Practica::create([
                'estudiante_id' => $plan->solicitude->estudiante_id,
                'docente_id' => $plan->solicitude->asesor_id,
                'solicitude_id' => $plan->solicitud_id,
                'plan_practica_id' => $plan->id,
                'empresa_id' => $plan->solicitude->empresa_id,
                'estado' => 'En desarrollo',
                'activo' => true
            ]);
        }
    }
}