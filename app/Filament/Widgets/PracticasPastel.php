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
   public static function canView(): bool
{
    /** @var \App\Models\User $user */
    $user = auth()->user();

    return auth()->check() && $user->hasAnyRole([
        'Director de escuela',
        'Secretaria',
        'Admin',
    ]);
}

    protected int | string | array $columnSpan = 1;
 
    protected function getContent(): string
    {
        return <<<HTML
        <div style="display: flex; flex-direction: column; align-items: center; min-height: 400px;">
            {$this->renderChart()}
        </div>
        HTML;
    }

    protected function getData(): array
    {
        $informesAprobados = InformeDePractica::where('estado', 'Aprobado')->count();
        $informesDesaprobados = InformeDePractica::where('estado', 'Desaprobado')->count();
        $planDesaprobados = PlanPractica::where('estado', 'Desaprobado')->count();
        $practicasDesarrollo = Practica::where('estado', 'En Desarrollo')->count();
        $planEnProceso = PlanPractica::whereNotIn('estado', ['Desaprobado', 'Aprobado'])->count();

        return [
            'datasets' => [
                [
                    'data' => [
                        $informesAprobados,
                        $informesDesaprobados,
                        $planDesaprobados,
                        $practicasDesarrollo,
                        $planEnProceso,
                    ],
                    'backgroundColor' => ['#10B981', '#EF4444', '#F87171', '#60A5FA', '#FBBF24'],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Prácticas Aprobados',
                'Prácticas Desaprobados',
                'Planes de Prácticas Desaprobados',
                'Prácticas en Desarrollo          ',
                'Planes de Prácticas en Proceso',
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'cutout' => '70%',
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                        'labels' => [
                            'font' => [
                                'weight' => 'bold',
                                'size' => 14
                            ],
                            'generateLabels' => RawJs::make('function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: label + " " + data.datasets[0].data[i],
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: isNaN(data.datasets[0].data[i]) || data.datasets[0].data[i] <= 0,
                                    index: i
                                }));
                            }')
                        ],
                    ],
                    'tooltip' => [
                        'enabled' => true,
                        'callbacks' => [
                            'label' => RawJs::make('function(context) {
                                return context.label + " " + context.raw;
                            }'),
                            'title' => RawJs::make('function() { return ""; }') // Elimina el título del tooltip
                        ]
                    ],
                    'datalabels' => [
                        'display' => false
                    ]
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}