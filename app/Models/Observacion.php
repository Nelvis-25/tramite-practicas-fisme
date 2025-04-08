<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Observacion extends Model
{ 
    use HasFactory;
    protected $table = 'observacions';
    protected $fillable = ['solicitud_id', 'mensaje'];
    
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }
}