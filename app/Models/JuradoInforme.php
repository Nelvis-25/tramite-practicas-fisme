<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuradoInforme extends Model
{
    use HasFactory;

    protected $table = 'jurado_informes';

    protected $fillable = [
        'nombre',
        'fechainicio',
        'fechafin',
        'estado',
    ]; 
    
    public function integrante()
    {
        return $this->hasMany(Integrante::class);
    } 
    public function informePracticas()
    {
        return $this->hasMany(InformePractica::class);
    }
}
