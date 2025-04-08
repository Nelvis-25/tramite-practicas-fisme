<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanDePractica extends Model
{
    use HasFactory;
    protected $table = 'plan_de_practicas';
    protected $fillable = [
        'solicitud_id',
        'comision_permanente_id',
        'fecha_resolucion',
        'fecha_entrega_a_docentes',
        'fecha_sustentacion',
        'estado'
    ];
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
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
    public function tipoEstudiante()
{
    return $this->belongsTo(TipoEstudiante::class);
}
    
}
