<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InformeDePractica;
use App\Models\EvaluacionDeInforme;

class ActaController extends Controller
{
    public function verActa($id)
    {
        $evaluacion = EvaluacionDeInforme::with([
            'informeDePractica.solicitudInforme.estudiante',
            'informeDePractica.solicitudInforme.practica.solicitude',
            'informeDePractica',
            'jurados.docente',
            
        ])->find($id);

        if (!$evaluacion) {
            return 'No se encontró la EvaluacionDeInforme con ID: ' . $id;
        }

        $informe = $evaluacion->informeDePractica;
         $juradosDeLaRonda = EvaluacionDeInforme::with('jurados.docente')
        ->where('informe_de_practica_id', $informe->id)
        ->where('ronda', $evaluacion->ronda)
        ->get()
        ;

                    // Calcular promedio en el controlador
            $rondaActual = $evaluacion->ronda;

            $evaluacionesRonda = $informe->evaluaciones()
                ->where('ronda', $rondaActual)
                ->where('estado', 'Evaluado')
                ->get();

            $promedioRonda = '-';

            if ($evaluacionesRonda->count() >= 3) {
                $promedioRonda = round($evaluacionesRonda->avg('nota'));
            }
        return Pdf::loadView('pdf.acta', [
            'informe' => $informe,
            'evaluacion' => $evaluacion,
            'juradosDeLaRonda' => $juradosDeLaRonda,
            'promedioRonda' => $promedioRonda,
            'horaActual' => now()->format('H:i a'),
            
        ])->stream('acta.pdf');
    }
}