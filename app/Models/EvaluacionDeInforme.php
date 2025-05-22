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
        'observacion',
        'estado',
        'activo',
    ];

    public function jurados()
    {
        return $this->belongsTo(JuradoDeInforme::class, 'jurado_de_informe_id' );
    }

    public function informeDePractica()
    {
        return $this->belongsTo(InformeDePractica::class, 'informe_de_practica_id');
    }
    public function observaciones()
        {
            return $this->hasMany(ObservacionEvaluacion::class, 'evaluacion_de_informe_id');
        }

    protected static function booted()
    {
        static::updated(function ($evaluacioinforme) {
            if (!in_array($evaluacioinforme->estado, ['Aprobado', 'Observado', 'Desaprobado'])) {
                return;
            }

            $evaluacioinforme->actualizarEstadoPlanPadre();
        });
    }
     public function actualizarEstadoPlanPadre()
    {
        $plan = $this->informeDePractica;
        $evaluacionesCompletadas = $plan->evaluaciones()
            ->whereIn('estado', ['Aprobado', 'Observado', 'Desaprobado'])
            ->get();

        if ($evaluacionesCompletadas->count() < 3) {
            return; 
        }
        if ($evaluacionesCompletadas->contains('estado', 'Desaprobado')) {
            $nuevoEstado = 'Desaprobado';
        } elseif ($evaluacionesCompletadas->contains('estado', 'Observado')) {
            $nuevoEstado = 'Observado';
        } else {
            $nuevoEstado = 'Aprobado';
        }
        $plan->updateQuietly(
            [
                'estado' => $nuevoEstado,
                'observaciones' => 'Sustentado',
            ]);

        $plan->evaluaciones()
            ->where('estado', 'Pendiente')
            ->delete();

        if ($plan->solicitudInforme && $plan->solicitudInforme->practica) {
            $plan->solicitudInforme->practica->updateQuietly([
                'estado' => 'Finalizado',
            ]);
        }

    }
}
