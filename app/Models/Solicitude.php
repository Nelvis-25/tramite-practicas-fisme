<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Solicitude extends Model
{
    use HasFactory;
    protected $table = 'solicitudes';
    protected $fillable = [
        'nombre',
        'estudiante_id',
        'linea_investigacion_id',
        'asesor_id',
        'fecha_inicio',
        'fecha_fin',
        'empresa',
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

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(Requisito::class);
    }
    public function observacions()
    {
        return $this->hasMany(Observacion::class)->latest();
    }
    public function requisitos()
    {
        return $this->belongsToMany(Requisito::class, 'validacions');
    }
    
    public function practicas()
{
    return $this->hasMany(Practica::class, 'docente_id');
}

    protected static function boot()
    {
        parent::boot();
    
        static::updated(function ($solicitude) {
            // Si el estado de la solicitud es 'Validado' o 'Rechazado', actualizar el estado.
            
        });
    }
}
