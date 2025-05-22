<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservacionEvaluacion extends Model
{
    use HasFactory;
    protected $table = 'observacion_evaluacions';
    protected $fillable = [
        'evaluacion_de_informe_id',
        'observacion',
    ];
    public function evaluacionDeInforme()
    {
        return $this->belongsTo(EvaluacionDeInforme::class, 'evaluacion_de_informe_id');
    }
}
