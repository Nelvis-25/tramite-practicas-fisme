<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaInvestigacion extends Model
{
    use HasFactory;
    protected $table = 'linea_investigacions';
    protected $fillable = ['nombre', 'estado'];
    
    protected $casts = [
        'estado' => 'boolean'
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }
}
