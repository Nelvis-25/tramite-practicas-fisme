<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Models\InformeDePractica;
use Illuminate\Support\Facades\Http;

class EnviarRecordatoriasInformeWhatsapp extends Command
{
    protected $signature = 'app:enviar-recordatorias-informe-whatsapp';
    protected $description = 'Envía recordatorios por WhatsApp a los estudiantes que sustentan su informe mañana';

    public function handle()
    {
        $maniana = Carbon::tomorrow()->toDateString();

        $informes = InformeDePractica::with('solicitudInforme.estudiante', 'jurados.docente')
            ->whereDate('fecha_sustentacion', $maniana)
            ->get();

        if ($informes->isEmpty()) {
            $this->info('📭 No hay sustentaciones de informes programadas para mañana.');
            return;
        }

        foreach ($informes as $informe) {
            $estudiante = $informe->solicitudInforme->estudiante;

            if (!$estudiante || !$estudiante->telefono) {
                $this->warn('⚠️ Estudiante sin teléfono: ' . ($estudiante->nombre ?? 'Desconocido'));
                continue;
            }

            $presidente = $informe->jurados->firstWhere('cargo', 'Presidente');
            if (!$presidente || !$presidente->docente) {
                $this->warn('⚠️ No se encontró presidente para el informe ID ' . $informe->id);
                continue;
            }

            $hora = Carbon::parse($informe->fecha_sustentacion)->format('h:i a');
            $fecha = Carbon::parse($informe->fecha_sustentacion)->format('d/m/Y');

            $mensaje = "📢 *Recordatorio de Sustentación de Informe de Prácticas*\n\n";
            $mensaje .= "Hola *{$estudiante->nombre}*, te recordamos que mañana tienes programado/a la sustentación de tu informe de prácticas.\n\n";
            $mensaje .= "🕒 Fecha y hora: *$hora* ($fecha)\n";
            $mensaje .= " Lugar: Sala de reuniones FISME\n\n";
            $mensaje .= "Atentamente, Jurados de Informe de Prácticas.";

            $numero = '+51' . ltrim(preg_replace('/[^0-9]/', '', $estudiante->telefono), '0');

            $this->enviarWhatsapp($numero, $mensaje);
        }

        $this->info('✅ Todos los mensajes de recordatorio han sido enviados.');
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
