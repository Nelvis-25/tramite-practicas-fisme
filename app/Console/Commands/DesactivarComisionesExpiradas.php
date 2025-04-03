<?php

namespace App\Console\Commands;
use App\Models\ComisionPermanente;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DesactivarComisionesExpiradas extends Command
{
    protected $signature = 'comisiones:desactivar';
    protected $description = 'Desactiva comisiones al llegar a su fecha fin';

    public function handle()
    {
        $hoy = Carbon::today();
        
        ComisionPermanente::where('estado', true)
            ->whereDate('fecha_fin', '<=', $hoy)
            ->update(['estado' => false]);
        
        $this->info('Comisiones expiradas desactivadas: ' . $hoy->format('d/m/Y'));
    }
}