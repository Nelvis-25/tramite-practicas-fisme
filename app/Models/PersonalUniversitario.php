<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalUniversitario extends Model

{
    protected $table = 'personal_universitarios';
    use HasFactory;
    protected $fillable = [
        'nombre', 'dni', 'codigo', 'telefono', 'email', 'cargo', 'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'dni' => 'integer',
        'telefono' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->estado && $model->cargo) {
                $existing = self::where('cargo', $model->cargo)
                    ->where('estado', true)
                    ->where('id', '!=', $model->id)
                    ->first();

                if ($existing) {
                    throw new \Exception("Ya existe un {$model->cargo} activo ({$existing->nombre}). ¡Desactívalo primero!");
                }
            }
        });
    }
    public function comisionesCreadas()
    {
        return $this->hasMany(ComisionPermanente::class, 'director_id');
    }
}