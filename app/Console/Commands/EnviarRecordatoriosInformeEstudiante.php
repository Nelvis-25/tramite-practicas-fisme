<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Models\InformeDePractica;
use App\Mail\NotificacionInformeEstudiante;

class EnviarRecordatoriosInformeEstudiante extends Command
{
    protected $signature = 'app:enviar-recordatorios-informe-estudiante';

    protected $description = 'Envía recordatorios a estudiantes que sustentan su informe de práctica mañana';

    public function handle()
    {
        $maniana = Carbon::tomorrow()->toDateString();

        $informes = InformeDePractica::with('solicitudInforme.estudiante', 'jurados.docente')
            ->whereDate('fecha_sustentacion', $maniana)
            ->get();

        if ($informes->isEmpty()) {
            $this->info('No hay sustentaciones de informes programadas para mañana.');
            return;
        }

        foreach ($informes as $informe) {
            
            $estudiante = $informe->solicitudInforme->estudiante;

            if (!$estudiante || !$estudiante->email) {
                $this->warn('Estudiante sin correo: '  . ($estudiante->nombre ?? 'Desconocido'));
                continue;
            }

            $presidente = $informe->jurados
            ->firstWhere('cargo', 'Presidente');

            if (!$presidente || !$presidente->docente) {
                $this->warn("No se encontró presidente para el plan ID ' . $informe->id");
                continue;
            }
            $docente = $presidente->docente;
            $user = $docente->user;

            if (!$user || !$user->email) {
                $this->warn('Presidente sin usuario o email.');
                continue;
            }

            $fechaHora = Carbon::parse($informe->fecha_sustentacion)
                ->locale('es')
                ->translatedFormat('l d \d\e F \a \l\a\s h:i a');

            Mail::to($estudiante->email)->send(
                new NotificacionInformeEstudiante(
                fromEmail: $presidente->docente->email,
                fromName: $presidente->docente->nombre,
                nombreEstudiante: $estudiante->nombre,
                fechaHora: $fechaHora,
                nombreRemitente: $presidente->docente->nombre,
                cargoRemitente: $presidente->cargo
            ));

            $this->info("Correo enviado a: {$estudiante->email}");
        }

        $this->info('Todos los correos de informe han sido procesados.');
    }
}
