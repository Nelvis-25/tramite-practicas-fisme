<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Models\InformeDePractica;
use App\Mail\NotificacionJuradoInforme;
use App\Models\User;

class EnviarRecordatoriosJuradosInforme extends Command
{
    protected $signature = 'app:enviar-recordatorios-jurados-informe';
    protected $description = 'Envía correos a los jurados de informes de práctica con las sustentaciones del día siguiente';

    public function handle()
    {
        $maniana = Carbon::tomorrow()->format('Y-m-d');
        $this->info("📅 Buscando informes para: $maniana");

        $informes = InformeDePractica::with('solicitudInforme.estudiante', 'jurados.docente.user')
            ->whereDate('fecha_sustentacion', $maniana)
            ->get();

        if ($informes->isEmpty()) {
            $this->info('⚠️ No hay sustentaciones programadas para mañana.');
            return;
        }

        $juradosMap = [];

        foreach ($informes as $informe) {
            foreach ($informe->jurados as $jurado) {
                $docente = $jurado->docente;
                $user = $docente->user ?? null;

                if (!$docente || !$user || !$user->email) continue;

                $juradosMap[$user->email]['nombre'] = $docente->nombre;
                $juradosMap[$user->email]['cargo'] = $jurado->cargo;
                $juradosMap[$user->email]['sustentaciones'][] = [
                    'estudiante' => $informe->solicitudInforme->estudiante->nombre ?? 'Desconocido',
                    'fecha' => $informe->fecha_sustentacion,
                ];
            }
        }

        $remitente = User::role('Secretaria')->latest()->first();

        if (!$remitente) {
            $this->error('No se encontró una secretaria.');
            return;
        }

        foreach ($juradosMap as $email => $datos) {
            Mail::to($email)->send(new NotificacionJuradoInforme(
                $remitente->email,
                $remitente->name,
                $datos['nombre'],
                $datos['cargo'],
                $datos['sustentaciones']
            ));
            $this->info("✅ Correo enviado a jurado: {$datos['nombre']} ({$email})");
        }

        $this->info('✅ Todos los correos a jurados han sido enviados.');
    }
}
