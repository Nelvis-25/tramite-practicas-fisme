<?php

namespace App\Models;

use App\Filament\Resources\ValidacionResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validacion extends Model
{
    use HasFactory;
    protected $fillable = ['solicitud_id', 'requisito_id', 'entregado'];
    protected static string $resource = ValidacionResource::class;
 protected function getTableRecordUrlUsing(): ?\Closure
    {
        // Esto previene que cualquier fila redirija al hacer clic
        return fn (Model $record) => null;
    }
    public function solicitud()
{
    return $this->belongsTo(Solicitud::class);
}

public function requisito()
{
    return $this->belongsTo(Requisito::class);
}
}
