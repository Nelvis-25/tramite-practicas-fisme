<?php

namespace App\Filament\Widgets;

use App\Models\InformeDePractica;
use App\Models\PlanPractica;
use App\Models\Practica;
use App\Models\Solicitude;
use App\Models\SolicitudInforme;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PracticasTotal extends BaseWidget
{
    protected static ?int $sort = 1;
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
    protected function getStats(): array
    {
          $totalSolicitudes = Solicitude::where('estado', '!=', 'Pendiente')->count();
           $totalPlan = PlanPractica::where('estado', '!=', 'Aprobado')->count();
           $totalPractica = Practica::count();
           
        return [
           Stat::make('Total de prácticas registradas', $totalSolicitudes)
                ->description('Total confirmado')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'flex flex-col items-center justify-center text-center bg-white text-gray-900 border border-gray-300 shadow-sm rounded-md',
                ]),
            Stat::make('Plan de prácticas', $totalPlan)
                ->description('Total confirmado')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->extraAttributes([
                        'class' => 'flex flex-col items-center justify-center text-center bg-white text-gray-900 border border-gray-300 shadow-sm rounded-md',
                    ]),
            Stat::make('Informe y desarrollo de prácticas', $totalPractica )
                ->description('Total confirmado')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->extraAttributes([
                        'class' => 'flex flex-col items-center justify-center text-center bg-white text-gray-900 border border-gray-300 shadow-sm rounded-md',
                    ]),
            //
        ];
    }
}
