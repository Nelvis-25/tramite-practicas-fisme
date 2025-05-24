<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservacionEvaluacionPlan extends Model
{
    use HasFactory;
     protected $table = 'observacion_evaluacion_plans';
     protected $fillable = [
                'evaluacion_plan_id',
                 'observacion',
        ];
    public function evaluacionPlanDePractica()
        {
            return $this->belongsTo(EvaluacionPlanDePractica::class, 'evaluacion_plan_id');
            }
}       
