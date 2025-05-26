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

            // Paso 1: obtener la ronda más alta (última)
            $ultimaRonda = $plan->evaluaciones()->max('ronda');

            // Paso 2: obtener evaluaciones de esa última ronda con estado Evaluado
            $evaluacionesRonda = $plan->evaluaciones()
                ->where('ronda', $ultimaRonda)
                ->where('estado', 'Evaluado')
                ->get();

            if ($evaluacionesRonda->count() < 3) {
                return; // Aún no hay suficientes evaluaciones para proceder
            }

            // Paso 3: eliminar las pendientes de esa ronda
            $plan->evaluaciones()
                ->where('ronda', $ultimaRonda)
                ->where('estado', 'Pendiente')
                ->delete();

            // Paso 4: calcular promedio de las notas de esa ronda
            $promedio = $evaluacionesRonda->avg('nota');
            $promedioRedondeado = round($promedio);

            // Paso 5: decidir nuevo estado según promedio
            $nuevoEstado = ($promedioRedondeado < 12) ? 'Desaprobado' : 'Aprobado';

            // Paso 6: actualizar informe de práctica
            $plan->updateQuietly([
                'estado' => $nuevoEstado,
                'observaciones' => 'Sustentado',
            ]);

            // Paso 7: actualizar práctica como finalizada
            if ($plan->solicitudInforme && $plan->solicitudInforme->practica) {
                $plan->solicitudInforme->practica->updateQuietly([
                    'estado' => 'Finalizado',
                    'activo' => false,
                ]);
            }
        }

}
