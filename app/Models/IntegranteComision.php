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

    
    public function docente()
{
    return $this->belongsTo(Docente::class);
}


    public function comisionPermanente()
    {
        return $this->belongsTo(ComisionPermanente::class);
    }
    protected static function boot()
{
    parent::boot();

    static::saving(function ($model) {
        if (in_array($model->cargo, ['Presidente', 'Secretario'])) {
            $exists = self::where('comision_permanente_id', $model->comision_permanente_id)
                ->where('cargo', $model->cargo)
                ->when($model->exists, fn($query) => $query->where('id', '!=', $model->id))
                ->exists();

            if ($exists) {
                throw new \Exception("Ya existe un {$model->cargo} en esta comisión.");
            }
        }
    });
}

}
