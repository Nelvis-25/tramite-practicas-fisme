<?php

namespace App\Exports;

use App\Models\InformeDePractica;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InformeDePracticaExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return InformeDePractica::with('jurados.docente', 'solicitudInforme.estudiante')
            ->get()
            ->map(function ($informe) {
                return [
                    'Estudiante' => $informe->solicitudInforme->estudiante->nombre ?? 'Desconocido',
                    'Título de práctica' => wordwrap($informe->titulo, 60, "\n", true),
                    'Jurados' => $informe->jurados->map(fn($j) =>
                        $j->docente->grado_academico . ' ' . $j->docente->nombre
                    )->implode("\n"),
                    'Cargos' => $informe->jurados->map(fn($j) => $j->cargo)->implode("\n"),
                    'Estado' => $informe->estado,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Estudiante',
            'Título de práctica',
            'Jurados',
            'Cargos',
            'Estado',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:E1000')->getAlignment()->setWrapText(true); // Ajustar texto en todas las celdas
        $sheet->getColumnDimension('B')->setWidth(60); // Ancho para el título
        $sheet->getColumnDimension('C')->setWidth(40); // Ancho para jurados
        $sheet->getColumnDimension('D')->setWidth(20); // Ancho para cargos

        return [];
    }
}
