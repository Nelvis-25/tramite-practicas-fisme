<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use App\Models\InformeDePractica;
use App\Models\User;

class EnviarRecordatoriasInformeJuradoWhatsapp extends Command
{
    protected $signature = 'app:enviar-recordatorias-informe-jurado-whatsapp';
    protected $description = 'Envía mensajes de WhatsApp a jurados con sustentaciones de informe programadas para mañana';

    public function handle()
    {
        $maniana = Carbon::tomorrow()->format('Y-m-d');
        $this->info("📅 Buscando sustentaciones para: $maniana");

        $informes = InformeDePractica::with('solicitudInforme.estudiante', 'jurados.docente.user')
            ->whereDate('fecha_sustentacion', $maniana)
            ->get();

        if ($informes->isEmpty()) {
            $this->warn('⚠️ No hay sustentaciones programadas para mañana.');
            return;
        }

        $juradosMap = [];

        foreach ($informes as $informe) {
            foreach ($informe->jurados as $jurado) {
                $docente = $jurado->docente;
                $user = $docente->user ?? null;

                if (!$docente || !$user || !$user->telefono) continue;

                $juradosMap[$user->telefono]['nombre'] = $docente->nombre;
                $juradosMap[$user->telefono]['cargo'] = $jurado->cargo;
                $juradosMap[$user->telefono]['sustentaciones'][] = [
                    'estudiante' => $informe->solicitudInforme->estudiante->nombre ?? 'Desconocido',
                    'fecha' => $informe->fecha_sustentacion,
                ];
            }
        }

        foreach ($juradosMap as $telefono => $datos) {
            $mensaje = "📢 *Recordatorio de Sustentaciones de Informe de Prácticas*\n";
            $mensaje .= "Hola *{$datos['nombre']}* ({$datos['cargo']}) miembro de jurados de Informes de Prácticas , mañana tienes las siguientes sustentaciones asignadas:\n\n";

            foreach ($datos['sustentaciones'] as $sustentacion) {
                $hora = Carbon::parse($sustentacion['fecha'])->format('h:i a');
                $fecha = Carbon::parse($sustentacion['fecha'])->format('d/m/Y');
                $mensaje .= "👨‍🎓 *{$sustentacion['estudiante']}* a las {$hora} ({$fecha})\n";
            }

            $mensaje .= "\n *Lugar:* Sala de reuniones FISME.";

            $this->enviarWhatsapp($telefono, $mensaje);
            $this->info("✅ Mensaje enviado a: {$datos['nombre']} ({$telefono})");
        }

        $this->info('✅ Todos los mensajes han sido enviados.');
    }

    private function enviarWhatsapp($telefono, $mensaje)
    {
        $instanceId = env('ULTRAMSG_INSTANCE_ID');
        $token = env('ULTRAMSG_TOKEN');

        $response = Http::asForm()->post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
            'token' => $token,
            'to' => $telefono,
            'body' => $mensaje,
        ]);

        if (!$response->successful()) {
            $this->error("❌ Error al enviar mensaje a $telefono: " . $response->body());
        }
    }
}
