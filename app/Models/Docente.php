<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'user_id',
        'cargo_id',
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    public function user(): BelongsTo

    {
        return $this->belongsTo(User::class);
    }


    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }
    public function integranteComision(): HasMany
    {
        return $this->hasMany(IntegranteComision::class);
    }
    
    public function solicitude()
{
    return $this->hasMany(Solicitude::class);
}
    public function practicas()
{
    return $this->hasMany(Practica::class, 'docente_id');
}
 // aca coloco la relacion con los jurados : 
 public function jurados()
    {
        return $this->hasMany(JuradoDeInforme::class);
    }

}
