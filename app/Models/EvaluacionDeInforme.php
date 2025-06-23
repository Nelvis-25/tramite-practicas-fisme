<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ObservacionInforme;
class EvaluacionDeInforme extends Model
{
    use HasFactory;
    protected $table = 'evaluacion_de_informes';
    protected $fillable = [
        'informe_de_practica_id',
        'jurado_de_informe_id',
        'fecha_evaluacion',
        'nota',
        'ronda',
        'observacion',
        'estado',
        'activo',
    ];
     protected $attributes = [
        'estado' => 'Evaluado',
    ];

    public function jurados()
    {
        return $this->belongsTo(JuradoDeInforme::class, 'jurado_de_informe_id' );
    }

    public function informeDePractica()
    {
        return $this->belongsTo(InformeDePractica::class, 'informe_de_practica_id');
    }
   

protected static function booted()
{
    static::created(function ($evaluacioinforme) {
        if ($evaluacioinforme->estado !== 'Evaluado') {
            return;
        }

        $evaluacioinforme->actualizarEstadoPlanPadre();
    });

    static::updated(function ($evaluacioinforme) {
        if ($evaluacioinforme->estado !== 'Evaluado') {
            return;
        }

        $evaluacioinforme->actualizarEstadoPlanPadre();
    });
}

        public function actualizarEstadoPlanPadre()
        {
            $plan = $this->informeDePractica;
            $ultimaRonda = $plan->evaluaciones()->max('ronda');
            $evaluacionesRonda = $plan->evaluaciones()
                ->where('ronda', $ultimaRonda)
                ->where('estado', 'Evaluado')
                ->get();

            if ($evaluacionesRonda->count() < 3) {
                return; 
            }

            $plan->evaluaciones()
                ->where('ronda', $ultimaRonda)
                ->where('estado', 'Pendiente')
                ->delete();
            $promedio = $evaluacionesRonda->avg('nota');
            $promedioRedondeado = round($promedio);
            $nuevoEstado = ($promedioRedondeado < 12) ? 'Desaprobado' : 'Aprobado';

            $plan->updateQuietly([
                'estado' => $nuevoEstado,
                'observaciones' => 'Sustentado',
            ]);

            
            if ($plan->solicitudInforme && $plan->solicitudInforme->practica) {
                $plan->solicitudInforme->practica->updateQuietly([
                    'estado' => 'Finalizado',
                    'activo' => false,
                ]);
            }
            //edesactivamos el estado de la solicitud
            if (
                $plan->solicitudInforme &&
                $plan->solicitudInforme->practica &&
                $plan->solicitudInforme->practica->solicitude
            ) {
                $plan->solicitudInforme->practica->solicitude->updateQuietly([
                    'activo' => false,
                ]);
            }
        }

}
