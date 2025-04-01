<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComisionPermanente extends Model
{
    use HasFactory;

    protected $table = 'comision_permanentes';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'director_id' // Asegúrate que coincide con tu migración
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'estado' => 'boolean'
    ];

    // Relación inversa con PersonalUniversitario
    public function director()
    {
        return $this->belongsTo(PersonalUniversitario::class, 'director_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // 1. Verificar que el asignado sea Director de Escuela
            if ($model->director && $model->director->cargo !== 'Director de escuela') {
                throw new \Exception('Solo los Directores de Escuela pueden crear comisiones');
            }

            // 2. Validar fechas (fin mayor que inicio)
            if ($model->fecha_fin <= $model->fecha_inicio) {
                throw new \Exception('La fecha de fin debe ser posterior a la fecha de inicio');
            }

            // 3. Validar que no exista otra comisión activa en el mismo período
            if ($model->estado) {
                $existing = self::where('id', '!=', $model->id)
                    ->where(function ($query) use ($model) {
                        $query->whereBetween('fecha_inicio', [$model->fecha_inicio, $model->fecha_fin])
                              ->orWhereBetween('fecha_fin', [$model->fecha_inicio, $model->fecha_fin]);
                    })
                    ->where('estado', true)
                    ->exists();

                if ($existing) {
                    throw new \Exception('Ya existe una comisión activa en este período');
                }
            }
        });
    }
    
}
