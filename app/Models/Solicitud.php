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
    
}
