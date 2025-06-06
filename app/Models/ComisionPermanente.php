<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComisionPermanente extends Model
{
    protected $table = 'comision_permanentes';
    
    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    protected $casts = [
        'fecha_inicio' => 'date:Y-m-d',
        'fecha_fin' => 'date:Y-m-d',
        'estado' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $now = Carbon::now();
            if ($model->fecha_fin && $model->fecha_fin->lt($now)) {
                $model->estado = false;
                return;
            }

            if ($model->estado) {
                $query = self::query()
                    ->where('estado', true)
                    ->where('fecha_fin', '>', $now);
                
                if ($model->exists) {
                    $query->where('id', '!=', $model->id);
                }

                if ($query->exists()) {
                    throw new \Exception(
                        $model->exists
                            ? 'No puede tener dos comisiones activas simultáneamente.'
                            : 'Ya existe una comisión activa. Desactívela primero .'
                    );
                }
            }
        });
    }

    // Helper para verificar si está vigente
    public function estaVigente(): bool
    {
        return $this->estado && $this->fecha_fin->gt(now());
    }

    public function integranteComision(): HasMany
    {
        return $this->hasMany(IntegranteComision::class);
    }
    // esto boras esto agrege  
    
}