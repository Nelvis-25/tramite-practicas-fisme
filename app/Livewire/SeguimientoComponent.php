<?php

namespace App\Livewire;

use App\Models\Estudiante;
use App\Models\Solicitude;
use App\Models\PlanPractica;
use App\Models\IntegranteComision;
use Carbon\Carbon;
use App\Models\EvaluacionPlanDePractica;
use App\Models\InformeDePractica;
use App\Models\SolicitudInforme;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SeguimientoComponent extends Component
{
    public $nombreSolicitud;
    public $fechaCreacion;
    public $estadoSolicitud;
    public $jurados = [];
    public $fechaSustentacion;
    public $observaciones;
    public $observacioneSolicitud;
    public $estadoPlan;
    public $evaluacionesConObservaciones = []; 

    // atributos del informe de practicas 
    public $fechaSolicitudInforme;
    public $estadoSolicitudInforme;
    public $observacionesSolicitudInforme;
    public $fechaSustentacionInforme;
    public $estadoInforme;
    public $juradosInforme = [];
    public $observacionesInforme;
     //contactos de la secretaria 
    public $nombreSecretaria;
    public $telefonoSecretaria;
    public function mount()
    {
        $user = Auth::user();
        /** @var User $user */
        if ($user && $user->hasRole('Estudiante')) {
            $estudiante = Estudiante::where('user_id', $user->id)->first();

            if ($estudiante) {
                $solicitud = Solicitude::where('estudiante_id', $estudiante->id)->latest()->first();
                $solicitudinforme = SolicitudInforme::where('estudiante_id', $estudiante->id)->latest()->first(); 
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

                if ($solicitudinforme) {
                   $this->fechaSolicitudInforme = $solicitudinforme->created_at->format('d/m/Y');
              
                    if (in_array($solicitudinforme->estado, ['Aceptado', 'Rechazado','Jurado asignado'])) {
                        $this->estadoSolicitudInforme = $solicitudinforme->estado;
                    }
                    if ($solicitudinforme->estado === 'Rechazado') {
                            $this->observacionesSolicitudInforme = $solicitudinforme->observaciones->last()->observacion ?? 'Sin observaciones registradas';
                        }
                    
                    $informe = InformeDePractica::with(['evaluaciones'])
                         ->where('solicitud_informe_id', $solicitudinforme->id)
                         ->first();
                     if ($informe) {
                        // ✅ Obtener fecha de sustentación si existe
                        $this->fechaSustentacionInforme = $informe->fecha_sustentacion 
                        ? Carbon::parse($informe->fecha_sustentacion)->format('d/m/Y H:i') 
                        : null;
                         $this->observacionesInforme = $plan->observaciones ?? null;
                        $this->estadoInforme = $informe->estado;
                         
                        //  Obtener jurados (jurados de informe )
                        $this->juradosInforme = $informe->jurados()
                        ->with('docente') // Cargar relación docente
                        ->get()
                        ->map(function ($jurado) {
                            return [
                                'nombre' => $jurado->docente->nombre ?? 'No asignado',
                                'cargo' => $jurado->cargo ?? 'Sin cargo definido'
                            ];
                        })->toArray();
                    }  
                }
            }
        }
        $secretaria = User::role('Secretaria')->latest()->first();

        if ($secretaria && $secretaria->docente) {
            $this->nombreSecretaria = $secretaria->docente->nombre;
            $this->telefonoSecretaria = $secretaria->docente->telefono ?? $secretaria->email;
        }
    }

    public function render()
    {
        return view('livewire.seguimiento-component');
    }
}
