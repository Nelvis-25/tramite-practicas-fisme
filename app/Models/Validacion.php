<?php

namespace App\Models;

use App\Filament\Resources\ValidacionResource;
use Filament\Forms\Components\Livewire;
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
 

public function requisito()
{
    return $this->belongsTo(Requisito::class);
}
protected static function booted()
{
    static::updating(function ($model) {
        if ($model->isDirty('entregado')) {
            // Disparar evento Livewire
            Livewire::dispatch('checkboxUpdated', [
                'id' => $model->id,
                'state' => $model->entregado
            ]);
        }
    });
}
}
