<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integrante extends Model
{
    use HasFactory;
     protected $table = 'integrantes';
    protected $fillable = [
        'jurado_informe_id',
        'docente_id',
        'cargo',
    ];
    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }
    public function juradoInforme()
    {
        return $this->belongsTo(JuradoInforme::class);
    }
    
    public function evaluacionInforme()
    {
        return $this->belongsTo(EvaluacionInforme::class, );
    }
}  
