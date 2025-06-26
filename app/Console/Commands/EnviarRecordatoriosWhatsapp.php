<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlanPractica;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class EnviarRecordatoriosWhatsapp extends Command
{
    protected $signature = 'app:enviar-recordatorios-whatsapp';
    protected $description = 'Envía mensajes de WhatsApp recordatorios a estudiantes que sustentan mañana.';

    public function handle()
    {
        $maniana = Carbon::tomorrow()->toDateString();

        $planes = PlanPractica::with('solicitude.estudiante', 'comisionPermanente.integranteComision.docente')
            ->whereDate('fecha_sustentacion', $maniana)
            ->get();

        if ($planes->isEmpty()) {
            $this->info('📭 No hay sustentaciones programadas para mañana.');
            return;
        }

        foreach ($planes as $plan) {
            $estudiante = $plan->solicitude->estudiante;

            if (!$estudiante || !$estudiante->telefono) {
                $this->warn('⚠️ Estudiante sin número de teléfono: ' . ($estudiante->nombre ?? 'Desconocido'));
                continue;
            }

            // Buscar presidente de la comisión
            $presidente = $plan->comisionPermanente
                ->integranteComision
                ->firstWhere('cargo', 'Presidente');

            if (!$presidente || !$presidente->docente) {
                $this->warn('⚠️ No se encontró presidente para el plan ID ' . $plan->id);
                continue;
            }

            $docente = $presidente->docente;

            $fechaHora = Carbon::parse($plan->fecha_sustentacion)
                ->locale('es')
                ->translatedFormat('l d \d\e F \a \l\a\s h:i a');

            // Mensaje personalizado
            $mensaje = "📢 *Recordatorio de Sustentación de Plan de Prácticas *\n\nHola *{$estudiante->nombre}*, te recordamos que mañana tienes programada la sustentación de tu  plan de prácticas.\n\n Fecha y hora: *{$fechaHora}*\n Lugar: Sala de reuniones de la FISME\n\nAtentamente, la Comisión Permanente.";

            $telefono = '+51' . preg_replace('/[^0-9]/', '', $estudiante->telefono);

            $this->enviarWhatsapp($telefono, $mensaje);
        }

        $this->info('✅ Todos los mensajes de WhatsApp han sido procesados.');
    }

    private function enviarWhatsapp($numero, $mensaje)
    {
        $response = Http::asForm()->post("https://api.ultramsg.com/" . env('ULTRAMSG_INSTANCE_ID') . "/messages/chat", [
            'token' => env('ULTRAMSG_TOKEN'),
            'to' => $numero,
            'body' => $mensaje,
        ]);

        if ($response->successful()) {
            $this->info("📤 Mensaje enviado a $numero");
        } else {
            $this->error("❌ Error al enviar a $numero: " . $response->body());
        }
    }
}
