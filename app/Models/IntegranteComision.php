<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegranteComision extends Model
{
    use HasFactory;
    protected $table = 'integrante_comisions';
    
    protected $fillable = [
        'docente_id',
        'comision_permanente_id',
        'cargo'
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class);
    }

    public function comisionPermanente(): BelongsTo
    {
        return $this->belongsTo(ComisionPermanente::class);
    }

}
