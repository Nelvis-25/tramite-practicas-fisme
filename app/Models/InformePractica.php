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
        'observaciones',
        'estado',
    ];
    
    
    public function solicitudInforme()
        {
            return $this->belongsTo(SolicitudInforme::class, 'solicitud_informe_id');
        }
    
        public function integrante()
        {
            return $this->hasMany(Integrante::class);
        }
    
        public function juradoInforme()
          {
           return $this->belongsTo(JuradoInforme::class, 'jurado_informe_id');
          }
          public function docente()
          {
              return $this->belongsTo(Docente::class, 'docente_id');
          }
      
          public function evaluacionInforme()
          {
              return $this->hasMany(EvaluacionInforme::class, );
          }
          public function evaluaciones()
            {
                return $this->hasMany(\App\Models\EvaluacionInforme::class);
            }

           
            protected static function booted()
            {
                static::created(function ($informePractica) {
                    $jurados = Integrante::where('jurado_informe_id', $informePractica->jurado_informe_id)->get();
                    
                    foreach ($jurados as $jurado) {
                        $yaExiste = EvaluacionInforme::where('informe_practica_id', $informePractica->id)
                            ->where('integrante_id', $jurado->id)
                            ->exists();
            
                        if (!$yaExiste) {
                            // Si no existe, creamos la evaluación para el jurado
                            EvaluacionInforme::create([
                                'informe_practica_id' => $informePractica->id,
                                'integrante_id' => $jurado->id,
                                'estado' => 'Pendiente',
                                'observacion' => null,
                                'activo' => false,
                            ]);
                        }
                    }
                });
            }       
}
