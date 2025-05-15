<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Practica extends Model
{
    use HasFactory;
    protected $table = 'practicas';
    protected $fillable = [
        'estudiante_id',
        'docente_id',
        'solicitude_id',
        'plan_practica_id',
        'estado',
        'activo',
    ];
    protected $casts = [
    'activo' => 'boolean',
    ];
    public function solicitude()
    {
        return $this->belongsTo(Solicitude::class, 'solicitude_id');
    }
    
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }
    
 
    public function asesor()
    {
    return $this->belongsTo(Docente::class, 'docente_id');
     }
    
    
    
    public function planPractica()
    {
        return $this->belongsTo(PlanPractica::class, 'plan_practica_id');
    }
    public function solicitudInformes()
    {
        return $this->hasMany(SolicitudInforme::class, 'practica_id');
    }
   
// metodo para obtener y liverar alos acesores

}
