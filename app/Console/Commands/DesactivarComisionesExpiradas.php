<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ComisionPermanente;
use Carbon\Carbon;

class DesactivarComisionesExpiradas extends Command
{
    protected $signature = 'comisiones:desactivar-expiradas';

    protected $description = 'Desactiva automáticamente las comisiones cuyo periodo ya ha expirado.';

    public function handle()
    {
        $hoy = Carbon::now();

        $comisiones = ComisionPermanente::where('estado', true)
            ->where('fecha_fin', '<', $hoy)
            ->get();

        if ($comisiones->isEmpty()) {
            $this->info('No hay comisiones vencidas que desactivar.');
            return;
        }

        foreach ($comisiones as $comision) {
            $comision->estado = false;
            $comision->save();
        }

        $this->info('Se desactivaron ' . $comisiones->count() . ' comisiones vencidas.');
    }
  
}