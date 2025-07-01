<?php

namespace App\Exports;

use App\Models\PlanPractica;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlanDePracticaExport implements FromCollection, WithHeadings, WithStyles
{
     protected $selectedIds;

    public function __construct(array $selectedIds = [])
    {
        $this->selectedIds = $selectedIds;
    }
    public function collection()
    {
        return PlanPractica::with([
                'solicitude.estudiante',
                'solicitude.asesor',
                'comisionPermanente.integranteComision.docente'
            ])
            ->whereIn('id', $this->selectedIds)
            ->get()
            ->map(function ($plan) {
                $asesor = $plan->solicitude->asesor ?? null;
                $gradoAsesor = $asesor?->grado_academico ?? '';
                $nombreAsesor = $asesor?->nombre ?? 'Sin asesor';

                return [
                    'Est/Egre' => $plan->solicitude->estudiante->tipo_estudiante ?? 'Desconocido',
                    'Nombre del estudiante' => $plan->solicitude->estudiante->nombre ?? 'Desconocido',
                    'Asesor' => trim($gradoAsesor . ' ' . $nombreAsesor),
                    'Título de práctica' => wordwrap($plan->solicitude->nombre ?? 'Sin título', 60, "\n", true),
                    'Integrantes de la Comisión' => $plan->comisionPermanente?->integranteComision->map(function ($i) {
                        return trim(($i->docente->grado_academico ?? '') . ' ' . ($i->docente->nombre ?? ''));
                    })->implode("\n") ?? 'Sin integrantes',
                    'Cargo' => $plan->comisionPermanente?->integranteComision->map(fn($i) => $i->cargo)->implode("\n") ?? 'Sin cargos',
                    'Fecha entrega a docentes' => $plan->fecha_entrega_a_docentes ?? 'No asignado',
                    'Fecha de sustentación' => $plan->fecha_sustentacion ?? 'No asignado',
                    'Estado' => $plan->estado,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Est/Egre',
            'Nombre del estudiante',
            'Asesor',
            'Título de práctica',
            'Integrantes de la Comisión',
            'Cargo',
            'Fecha entrega a docentes',
            'Fecha de sustentación',
            'Estado',
        ];
    }

public function styles(Worksheet $sheet)
{
    $sheet->getStyle('A1:I1')->getFont()->setBold(true);
    $sheet->getStyle('A1:I1000')->getAlignment()->setWrapText(true);

    // Centrado horizontal y vertical para columnas B y C (Nombre del estudiante y Asesor) $sheet->getStyle('B2:B1000')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('A2:A1000')->getAlignment()->setVertical('center');
    $sheet->getStyle('B2:B1000')->getAlignment()->setVertical('center');
    //$sheet->getStyle('C2:C1000')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('C2:C1000')->getAlignment()->setVertical('center');
    $sheet->getStyle('G2:G1000')->getAlignment()->setVertical('center');
    $sheet->getStyle('H2:H1000')->getAlignment()->setVertical('center');
    $sheet->getStyle('I2:I1000')->getAlignment()->setVertical('center');

    // Anchos de columna
    $sheet->getColumnDimension('A')->setWidth(11);
    $sheet->getColumnDimension('B')->setWidth(29);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(50);
    $sheet->getColumnDimension('E')->setWidth(33);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(16);
    $sheet->getColumnDimension('I')->setWidth(10);

    return [];
}

}
