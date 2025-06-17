<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiantes';

    protected $fillable = [
        'nombre',
        'sexo',
        'dni',
        'codigo',
        'tipo_estudiante',
        'ciclo',
        'facultad',
        'carrera',
        'telefono',
        'email',
        'direccion',
        'estado',
        'user_id'
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

   

    public function scopeDeUsuario($query, $userId)
{
    return $query->where('user_id', $userId);
}

    public function solicitude(): HasMany
        {
            return $this->hasMany(Solicitude::class);
        }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function practicas()
{
    return $this->hasMany(Practica::class, 'docente_id');
}
public function solicitudInformes()
{
    return $this->hasMany(SolicitudInforme::class, 'estudiante_id');
}
   
}