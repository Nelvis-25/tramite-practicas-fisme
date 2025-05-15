<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPractica extends Model
{
    use HasFactory;
    protected $table = 'plan_practicas';
    protected $fillable = [
        'solicitude_id',
        'comision_permanente_id',
        'fecha_resolucion',
        'fecha_entrega_a_docentes',
        'fecha_sustentacion',
        'observaciones',
        'estado'
    ];
    public function solicitude()
    {
        return $this->belongsTo(Solicitude::class, 'solicitude_id');
    }
    public function asesor()
    {
        return $this->belongsTo(Docente::class, 'asesor_id');
    }
    public function comisionPermanente()
    {
        return $this->belongsTo(ComisionPermanente::class, 'comision_permanente_id');
    }
    public function integrantes()
    {
        return $this->hasMany(IntegranteComision::class);
    }
    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }
    public function evaluaciones()
    {
        return $this->hasMany(EvaluacionPlanDePractica::class);
    }

    public function practicas()
{
    return $this->hasMany(Practica::class, 'docente_id');
}

   
    protected static function booted()
            {
                static::created(function ($planPractica) {
                    $integrantes = IntegranteComision::where('comision_permanente_id', $planPractica->comision_permanente_id)->get();
                    foreach ($integrantes as $integrante) {
                        $yaExiste = EvaluacionPlanDePractica::where('plan_practica_id', $planPractica->id)
                            ->where('integrante_comision_id', $integrante->id)
                            ->exists();

                        if (!$yaExiste) {
                            EvaluacionPlanDePractica::create([
                                'plan_practica_id' => $planPractica->id,
                                'integrante_comision_id' => $integrante->id,
                                'estado' => 'Pendiente',
                                'observacion' => null,
                                'activo' => false,
                                
                            ]);
                        }
                    }
                });
                
                
            }

}