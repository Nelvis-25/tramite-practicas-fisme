<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuradoDeInforme extends Model
{
    use HasFactory;

    protected $table = 'jurado_de_informes';

    protected $fillable = [
        'informe_de_practica_id',
        'docente_id',
        'cargo',
    ];

public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

public function informeDePractica()
    {
        return $this->belongsTo(InformeDePractica::class);
    }
 public function evaluaciones()
    {
        return $this->hasMany(EvaluacionDeInforme::class, 'jurado_de_informe_id');
    }

}
