<?php

namespace App\Console\Commands;

use App\Models\PlanPractica;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class EnviarRecordatoriosComisionWhatsapp extends Command
{
    protected $signature = 'app:enviar-recordatorios-comision-whatsapp';
    protected $description = 'Envía mensajes de WhatsApp a los miembros de la comisión permanente con las sustentaciones del día siguiente';

    public function handle()
    {
        $maniana = Carbon::tomorrow()->format('Y-m-d');

        $planes = PlanPractica::with([
            'solicitude.estudiante',
            'comisionPermanente.integranteComision.docente.user'
        ])
        ->whereDate('fecha_sustentacion', $maniana)
        ->get();

        if ($planes->isEmpty()) {
            $this->info('No hay sustentaciones programadas para mañana.');
            return;
        }

        $comisiones = [];

        foreach ($planes as $plan) {
            foreach ($plan->comisionPermanente->integranteComision as $integrante) {
                $docente = $integrante->docente;

                if (!$docente || !$docente->telefono) continue;

                $telefono = '+51' .preg_replace('/[^0-9]/', '', $docente->telefono);
                $comisiones[$telefono]['nombre'] = $docente->nombre;
                $comisiones[$telefono]['cargo'] = $integrante->cargo;
                $comisiones[$telefono]['sustentaciones'][] = [
                    'estudiante' => $plan->solicitude->estudiante->nombre,
                    'fecha' => $plan->fecha_sustentacion,
                ];
            }
        }

        $instanceId = env('ULTRAMSG_INSTANCE_ID');
        $token = env('ULTRAMSG_TOKEN');

        foreach ($comisiones as $telefono => $datos) {
            $mensaje = "📢 *Recordatorio de Sustentaciones de Plan de Prácticas*\n";
            $mensaje .= "Hola *{$datos['nombre']}* ({$datos['cargo']}) de la comisión permanente, mañana tienes programadas las siguientes sustentaciones:\n\n";

            foreach ($datos['sustentaciones'] as $s) {
                $hora = Carbon::parse($s['fecha'])->format('h:i a');
                $fecha = Carbon::parse($s['fecha'])->format('d/m/Y');
                $mensaje .= "👨‍🎓 *{$s['estudiante']}* a las $hora ($fecha)\n";
            }

            $mensaje .= "\n Lugar: Sala de reuniones FISME.";

            $response = Http::asForm()->post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
                'token' => $token,
                'to' => $telefono,
                'body' => $mensaje,
            ]);

            if ($response->ok()) {
                $this->info("✅ Mensaje enviado a: $telefono");
            } else {
                $this->error("❌ Error al enviar a: $telefono — " . $response->body());
            }
        }

        $this->info('✅ Todos los mensajes de WhatsApp fueron enviados.');
    }
}
