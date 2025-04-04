<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    use HasFactory;
    protected $table = 'requisitos';
    protected $fillable = ['nombre', 'estado'];
    public function validaciones()
{
    return $this->hasMany(Validacion::class);
}
}
