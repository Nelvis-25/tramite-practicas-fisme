<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservacionInforme extends Model
{
    use HasFactory;

    protected $table = 'observacion_informes';
    protected $fillable = ['solicitud_informe_id', 'observacion'];


    public function solicitudInforme()
        {
            return $this->belongsTo(SolicitudInforme::class, 'solicitud_informe_id');
        }

}
