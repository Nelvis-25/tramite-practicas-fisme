<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformeDePractica extends Model
{
    use HasFactory;

    protected $table = 'informe_de_practicas';

    protected $fillable = [
        'solicitud_informe_id',
        'fecha_resolucion',
        'resolucion',
        'fecha_entrega_a_docentes',
        'fecha_sustentacion',
        'observaciones',
        'estado',
    ];

    public function solicitudInforme()
        {
            return $this->belongsTo(SolicitudInforme::class, );
        }
    public function jurados()
        {
            return $this->hasMany(JuradoDeInforme::class, );
        }
    public function evaluaciones()
    {
        return $this->hasMany(EvaluacionDeInforme::class, 'informe_de_practica_id');
    }

}
