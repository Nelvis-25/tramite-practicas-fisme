<?php

namespace App\Exports;

use App\Models\InformeDePractica;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InformeDePracticaExport implements FromCollection, WithHeadings, WithStyles
{
    protected $selectedIds;

    public function __construct(array $selectedIds = [])
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        return InformeDePractica::with([
                'jurados.docente',
                'solicitudInforme.estudiante',
                'solicitudInforme.practica.solicitude.asesor',
            ])
            ->whereIn('id', $this->selectedIds)
            ->get()
            ->map(function ($informe) {
                $asesor = $informe->solicitudInforme->practica->solicitude->asesor ?? null;
                $gradoAsesor = $asesor?->grado_academico ?? '';
                $nombreAsesor = $asesor?->nombre ?? 'Sin asesor';

                return [
                    'Est/Egre' => $informe->solicitudInforme->estudiante->tipo_estudiante ?? 'Desconocido',
                    'Nombre del estudiante' => $informe->solicitudInforme->estudiante->nombre ?? 'Desconocido',
                    'Asesor' => trim($gradoAsesor . ' ' . $nombreAsesor),
                    'Título de práctica' => wordwrap($informe->solicitudInforme->practica->solicitude->nombre ?? 'Sin título', 60, "\n", true),
                    'Jurados' => $informe->jurados->map(fn ($j) => $j->docente->grado_academico . ' ' . $j->docente->nombre)->implode("\n"),
                    'Cargos' => $informe->jurados->map(fn ($j) => $j->cargo)->implode("\n"),
                    'Fecha entrega a docentes' => $informe->fecha_entrega_a_docentes ?? 'No asignado',
                    'Fecha de sustentación' => $informe->fecha_sustentacion ?? 'No asignado',
                    'Estado' => $informe->estado,
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
            'Jurados',
            'Cargos',
            'Fecha entrega a docentes',
            'Fecha de sustentación',
            'Estado',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1000')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A2:A1000')->getAlignment()->setVertical('center');
        $sheet->getStyle('B2:B1000')->getAlignment()->setVertical('center');
        //$sheet->getStyle('C2:C1000')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('C2:C1000')->getAlignment()->setVertical('center');
        $sheet->getStyle('G2:G1000')->getAlignment()->setVertical('center');
        $sheet->getStyle('H2:H1000')->getAlignment()->setVertical('center');
        $sheet->getStyle('I2:I1000')->getAlignment()->setVertical('center');

        $sheet->getColumnDimension('A')->setWidth(11);  // Est/Egre
        $sheet->getColumnDimension('B')->setWidth(30);  // Nombre estudiante
        $sheet->getColumnDimension('C')->setWidth(31);  // Asesor
        $sheet->getColumnDimension('D')->setWidth(52);  // Título
        $sheet->getColumnDimension('E')->setWidth(33);  // Jurados
        $sheet->getColumnDimension('F')->setWidth(15);  // Cargos
        $sheet->getColumnDimension('G')->setWidth(15);  // Fecha entrega
        $sheet->getColumnDimension('H')->setWidth(16);  // Fecha sustentación
        $sheet->getColumnDimension('I')->setWidth(10);  // Estado

        return [];
    }
}
