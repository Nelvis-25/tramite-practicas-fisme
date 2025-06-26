<?php

namespace App\Console;

use App\Console\Commands\EnviarRecordatoriasInformeJuradoWhatsapp;
use App\Console\Commands\EnviarRecordatoriasInformeWhatsapp;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\EnviarRecordatoriosComision;
use App\Console\Commands\EnviarRecordatoriosComisionWhatsapp;
use App\Console\Commands\EnviarRecordatoriosInformeEstudiante;
use App\Console\Commands\EnviarRecordatoriosDeSustentacion;
use App\Console\Commands\EnviarRecordatoriosJuradosInforme;
use App\Console\Commands\EnviarRecordatoriosWhatsapp;

class Kernel extends ConsoleKernel

{
    
    protected $commands = [
        EnviarRecordatoriosDeSustentacion::class,
        EnviarRecordatoriosComision::class,
        EnviarRecordatoriosInformeEstudiante::class,
        EnviarRecordatoriosJuradosInforme::class,
        EnviarRecordatoriosWhatsapp::class,
        EnviarRecordatoriosComisionWhatsapp::class,
        EnviarRecordatoriasInformeWhatsapp::class,
        EnviarRecordatoriasInformeJuradoWhatsapp::class,
    ];
  

    protected function schedule(Schedule $schedule)
    {
        // Se ejecutará todos los días a las 08:00 a.m.
        $schedule->command('app:enviar-recordatorios-de-sustentacion')->dailyAt('09:00');
        $schedule->command('app:enviar-recordatorios-comision')->dailyAt('09:00');
        $schedule->command('app:enviar-recordatorios-informe-estudiante')->dailyAt('09:00');
        $schedule->command('app:enviar-recordatorios-jurados-informe')->dailyAt('09:00');
        $schedule->command('app:enviar-recordatorios-whatsapp')->dailyAt('09:00');
        $schedule->command('app:enviar-recordatorios-comision-whatsapp')->dailyAt('09:00');
        $schedule->command('app:enviar-recordatorias-informe-whatsapp')->dailyAt('09:00');
        $schedule->command('app:enviar-recordatorias-informe-jurado-whatsapp')->dailyAt('09:00');



    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
