<?php

namespace App\Console\Commands;

use App\Mail\NotificacionComision;
use App\Models\PlanPractica;
use App\Models\IntegranteComision;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class EnviarRecordatoriosComision extends Command
{
    protected $signature = 'app:enviar-recordatorios-comision';
    protected $description = 'Envía correos a los miembros de la comisión con las sustentaciones del día siguiente';

    public function handle()
    {
        $maniana = Carbon::tomorrow()->format('Y-m-d');

        // Obtener todos los planes de práctica que tengan sustentación mañana
        $planes = PlanPractica::with([
            'solicitude.estudiante',
            'comisionPermanente.integranteComision.docente'
        ])
        ->whereDate('fecha_sustentacion', $maniana)
        ->get();

        if ($planes->isEmpty()) {
            $this->info('No hay sustentaciones programadas para mañana.');
            return;
        }

        // Agrupar por correo de docente
        $comisiones = [];

        foreach ($planes as $plan) {
            foreach ($plan->comisionPermanente->integranteComision as $integrante) {
                $docente = $integrante->docente;

                if (!$docente || !$docente->email) continue;

                $comisiones[$docente->email]['nombre'] = $docente->nombre;
                $comisiones[$docente->email]['cargo'] = $integrante->cargo;
                $comisiones[$docente->email]['sustentaciones'][] = [
                    'estudiante' => $plan->solicitude->estudiante->nombre,
                    'fecha' => $plan->fecha_sustentacion,
                ];
            }
        }

        // Obtener la secretaria más reciente como remitente
        $remitente = \App\Models\User::role('Secretaria')->latest()->first();

        if (!$remitente) {
            $this->error('No se encontró una secretaria.');
            return;
        }

        // Enviar correos
        foreach ($comisiones as $email => $datos) {
            Mail::to($email)->send(new NotificacionComision(
                $remitente->email,
                $remitente->name,
                $datos['nombre'],
                $datos['cargo'],
                $datos['sustentaciones']
            ));
        }

        $this->info('Correos enviados a los miembros de la comisión.');
    }
}
