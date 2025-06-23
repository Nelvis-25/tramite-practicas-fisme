<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\EnviarRecordatoriosComision;
use App\Console\Commands\EnviarRecordatoriosInformeEstudiante;
use App\Console\Commands\EnviarRecordatoriosDeSustentacion;
use App\Console\Commands\EnviarRecordatoriosJuradosInforme;

class Kernel extends ConsoleKernel

{
    
    protected $commands = [
        EnviarRecordatoriosDeSustentacion::class,
        EnviarRecordatoriosComision::class,
        EnviarRecordatoriosInformeEstudiante::class,
        EnviarRecordatoriosJuradosInforme::class,
    ];
  

    protected function schedule(Schedule $schedule)
    {
        // Se ejecutará todos los días a las 08:00 a.m.
        $schedule->command('app:enviar-recordatorios-de-sustentacion')->dailyAt('08:00');
        $schedule->command('app:enviar-recordatorios-comision')->dailyAt('08:00');
        $schedule->command('app:enviar-recordatorios-informe-estudiante')->dailyAt('07:00');
        $schedule->command('app:enviar-recordatorios-jurados-informe')->dailyAt('07:00');


    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
