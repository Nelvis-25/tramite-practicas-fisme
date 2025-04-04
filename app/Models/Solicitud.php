<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitud extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'estudiante_id',
        'linea_investigacion_id',
        'asesor_id',
        'solicitud',
        'constancia',
        'informe',
        'carta_presentacion',
        'comprobante_pago',
        'estado',
    ];
    protected $attributes = [
        'estado' => 'Pendiente',
    ];
     public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
    public function lineaInvestigacion(): BelongsTo
    {
        return $this->belongsTo(LineaInvestigacion::class);
    }
    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }
    
    

public function requisitos()
{
    return $this->belongsToMany(Requisito::class, 'validacions');
}

public function validaciones() {
    return $this->hasMany(\App\Models\Validacion::class);
} 
protected static function boot()
{
    parent::boot();
    
    static::updated(function ($solicitud) {
        if ($solicitud->validaciones()->where('entregado', false)->doesntExist()) {
            $solicitud->update(['estado' => 'Validado']);
        }
    });
}
}
