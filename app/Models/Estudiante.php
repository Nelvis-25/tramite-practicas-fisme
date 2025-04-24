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
        'dni',
        'codigo',
        'tipo_estudiante_id',
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

   
    public function tipoEstudiante(): BelongsTo
    {
        return $this->belongsTo(TipoEstudiante::class);
    }
    public function scopeDeUsuario($query, $userId)
{
    return $query->where('user_id', $userId);
}

public function solicitude(): HasOne
{
    return $this->hasOne(Solicitude::class);
}
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

   
}