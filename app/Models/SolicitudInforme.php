<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudInforme extends Model
{
    use HasFactory;
    protected $table = 'solicitud_informes';
    protected $fillable = [
    
    'estudiante_id', 
    'practica_id', 
    'informe', 
    'solicitud',
    'resolucion', 
    'estado', 
    ];


    protected $attributes = [
        'estado' => 'Pendiente',
    ];
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function practica()
    {
        return $this->belongsTo(Practica::class);
    }

    public function informedePractica()    
        {
           return $this->hasMany(InformeDePractica::class,);
        }
    public function observaciones()
    {
        return $this->hasMany(ObservacionInforme::class, 'solicitud_informe_id');
    }

}
