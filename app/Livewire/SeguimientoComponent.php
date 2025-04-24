<?php

namespace App\Livewire;

use App\Models\Estudiante;
use App\Models\Solicitude;
use App\Models\PlanPractica;
use App\Models\IntegranteComision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SeguimientoComponent extends Component
{
    public $fechaCreacion;
    public $nombreSolicitud;
    public $estadoSolicitud;
    public $jurados = [];
    public $fechaSustentacion;

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

                    if (in_array($solicitud->estado, ['Validado', 'Rechazado','Asignado'])) {
                        $this->estadoSolicitud = $solicitud->estado;
                    }

                    // 🔍 Buscar plan de práctica relacionado
                    $plan = PlanPractica::where('solicitude_id', $solicitud->id)->first();

                    if ($plan) {
                        // ✅ Obtener fecha de sustentación si existe
                        $this->fechaSustentacion = $plan->fecha_sustentacion 
                        ? Carbon::parse($plan->fecha_sustentacion)->format('d/m/Y H:i') 
                        : null;

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
