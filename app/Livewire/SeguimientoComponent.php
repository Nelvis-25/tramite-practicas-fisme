<?php

namespace App\Livewire;

use App\Models\Estudiante;
use App\Models\Solicitude;
use App\Models\PlanPractica;
use App\Models\IntegranteComision;
use Carbon\Carbon;
use App\Models\EvaluacionPlanDePractica;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SeguimientoComponent extends Component
{
    public $fechaCreacion;
    public $nombreSolicitud;
    public $estadoSolicitud;
    public $jurados = [];
    public $fechaSustentacion;
    public $observaciones;
    public $observacioneSolicitud;
    public $estadoPlan;
     public $evaluacionesConObservaciones = []; 
    public function mount()
    {
        $user = Auth::user();
        /** @var User $user */
        if ($user && $user->hasRole('Estudiante')) {
            $estudiante = Estudiante::where('user_id', $user->id)->first();

            if ($estudiante) {
                $solicitud = Solicitude::where('estudiante_id', $estudiante->id)->latest()->first();

                if ($solicitud) {
                    $this->fechaCreacion = $solicitud->created_at->format('d/m/Y');
                    $this->nombreSolicitud = $solicitud->nombre;
              
                    if (in_array($solicitud->estado, ['Aceptado', 'Rechazado','Comisión asignada'])) {
                        $this->estadoSolicitud = $solicitud->estado;
                    }
                    if ($solicitud->estado === 'Rechazado') {
                            $this->observacioneSolicitud = $solicitud->observacions->first()->mensaje ?? 'Sin observaciones registradas';
                        }

                    // 🔍 Buscar plan de práctica relacionado
                    
                     $plan = PlanPractica::with(['evaluaciones.observaciones'])
                                ->where('solicitude_id', $solicitud->id)
                                ->first();
                    if ($plan) {
                        // ✅ Obtener fecha de sustentación si existe
                        $this->fechaSustentacion = $plan->fecha_sustentacion 
                        ? Carbon::parse($plan->fecha_sustentacion)->format('d/m/Y H:i') 
                        : null;
                        $this->observaciones = $plan->observaciones ?? null;
                        $this->estadoPlan = $plan->estado;

                        // Obtener observaciones cuando el estado es "Observado"
                        if ($plan->estado === 'Observado') {
                            $this->evaluacionesConObservaciones = $plan->evaluaciones
                                ->filter(function($evaluacion) {
                                    return $evaluacion->observaciones->isNotEmpty();
                                })
                                ->map(function($evaluacion) {
                                    return [
                                        'numero' => $evaluacion->numero_evaluacion,
                                        'observaciones' => $evaluacion->observaciones->pluck('observacion') // Cambiado a 'observacion'
                                    ];
                                })->toArray();
                        }


                        if ($plan->comision_permanente_id) {
                            // 🔍 Obtener jurados (integrantes de la comisión)
                            $integrantes = IntegranteComision::with('docente')
                                ->where('comision_permanente_id', $plan->comision_permanente_id)
                                ->get();

                            $this->jurados = $integrantes->map(function ($item) {
                                return [
                                    'nombre' => $item->docente->nombre ?? 'No asignado',
                                    'cargo' => $item->cargo,
                                ];
                            })->toArray();
                        }
                    }
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.seguimiento-component');
    }
}
