<?php

namespace App\Filament\Widgets;

use App\Models\InformeDePractica;
use App\Models\PlanPractica;
use App\Models\Practica;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class PracticasPastel extends ChartWidget
{
    protected static ?string $heading = 'ESTADO GENERAL DE PRÁCTICAS';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    protected function getContent(): string
    {
        return '<div style="max-width: 300px; max-height: 300px; margin: 0 auto;">' . parent::getContent() . '</div>';
    }

protected function getData(): array
{
    $informesAprobados = InformeDePractica::where('estado', 'Aprobado')->count();
    $informesDesaprobados = InformeDePractica::where('estado', 'Desaprobado')->count();
    $planDesaprobados = PlanPractica::where('estado', 'Desaprobado')->count();
    $practicasDesarrollo = Practica::where('estado', 'En desarrollo')->count();
    $planEnProceso = PlanPractica::whereNotIn('estado', ['Desaprobado', 'Aprobado'])->count();

 return [
        'datasets' => [
            [
                'label' => 'Estado de prácticas',
                'data' => [$informesAprobados, $informesDesaprobados, $planDesaprobados, $practicasDesarrollo, $planEnProceso],
                'backgroundColor' => ['#10B981', '#EF4444', '#F87171', '#60A5FA', '#FBBF24'],
            ],
        ],
        'labels' => [
            'Informes Aprobados',
            'Informes Desaprobados',
            'Planes Desaprobados',
            'Prácticas en Desarrollo',
            'Planes en Proceso',
        ],
        'options' => [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => ['font' => ['size' => 10]],
                ],
                'datalabels' => [
                    'display' => true,
                    'color' => '#fff',
                    'font' => [
                        'weight' => 'bold',
                        'size' => 14,
                    ],
                    'formatter' => RawJs::make(<<<'JS'
                        function(value, context) {
                            return value > 0 ? value : '';
                        }
                    JS),
                ],
            ],
        ],
        'plugins' => ['datalabels'], // 👈 Esto va al mismo nivel que 'datasets' y 'labels'
    ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
