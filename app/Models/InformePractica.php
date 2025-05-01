<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformePractica extends Model
{
    use HasFactory;
    protected $table = 'informe_practicas';
    protected $fillable = [
        'solicitud_informe_id',
        'jurado_informe_id',
        'fecha_resolucion',
        'fecha_entrega_a_docentes',
        'fecha_sustentacion',
        'estado',
    ];
    
    
    public function solicitudInforme()
        {
            return $this->belongsTo(SolicitudInforme::class);
        }
    
        public function integrante()
        {
            return $this->belongsTo(Integrante::class);
        }
    
        public function juradoInforme()
          {
           return $this->belongsTo(JuradoInforme::class);
          }
        
          public function evaluacionInforme()
          {
              return $this->belongsTo(EvaluacionInforme::class, );
          }
          public function evaluaciones()
{
    return $this->hasMany(\App\Models\EvaluacionInforme::class);
}
          
        }
