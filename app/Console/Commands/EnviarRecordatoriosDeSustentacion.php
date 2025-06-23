<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlanPractica;
use App\Models\User;
use App\Models\Docente;
use App\Mail\NotificacionDeRecordatoria;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class EnviarRecordatoriosDeSustentacion extends Command
{
    protected $signature = 'app:enviar-recordatorios-de-sustentacion';

    protected $description = 'Envía correos recordatorios a estudiantes que sustentan mañana';

    public function handle()
    {
        $maniana = Carbon::tomorrow()->toDateString();

        $planes = PlanPractica::with('solicitude.estudiante', 'comisionPermanente.integranteComision.docente')
            ->whereDate('fecha_sustentacion', $maniana)
            ->get();

        if ($planes->isEmpty()) {
            $this->info('No hay sustentaciones programadas para mañana.');
            return;
        }

        foreach ($planes as $plan) {
            $estudiante = $plan->solicitude->estudiante;

            if (!$estudiante || !$estudiante->email) {
                $this->warn('Estudiante sin correo: ' . ($estudiante->nombre ?? 'Desconocido'));
                continue;
            }

            // Buscar presidente de la comisión
            $presidente = $plan->comisionPermanente
                ->integranteComision
                ->firstWhere('cargo', 'Presidente');

            if (!$presidente || !$presidente->docente) {
                $this->warn('No se encontró presidente para el plan ID ' . $plan->id);
                continue;
            }

            $docente = $presidente->docente;
            $user = $docente->user;

            if (!$user || !$user->email) {
                $this->warn('Presidente sin usuario o email.');
                continue;
            }

            $fechaHora = Carbon::parse($plan->fecha_sustentacion)
                ->locale('es')
                ->translatedFormat('l d \d\e F \a \l\a\s h:i a');

            Mail::to($estudiante->email)->send(
                new NotificacionDeRecordatoria(
                    fromEmail: $user->email,
                    fromName: $user->name,
                    nombreEstudiante: $estudiante->nombre,
                    fechaHora: $fechaHora,
                    nombreRemitente: $docente->nombre,
                    cargoRemitente: $presidente->cargo
                )
            );

            $this->info('Correo enviado a: ' . $estudiante->email);
        }

        $this->info('✅ Todos los correos han sido procesados.');
    }
}
