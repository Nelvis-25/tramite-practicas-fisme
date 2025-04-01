<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;
    protected $table = 'docentes';
    protected $fillable = [
        'nombre',
        'dni',
        'codigo',
        'telefono',
        'email',
        'especialidad',
        'grado_academico',
        'tipo_contrato',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];
    public $timestamps = true;
}
