<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Filament::serving(function () {
            // Mostrar nombre del usuario conectado (opcional)
            Filament::registerRenderHook(
                'panels::topbar.end',
                function () {
                    if (Auth::check()) {
                        $user = Auth::user();
                        return '<span class="text-sm text-blue-600 mr-4">'
                            . e($user->name)
                            . '</span>';
                    }
                    return '';
                }
            );

            // Inyectar CSS inline para tablas compactas
Filament::registerRenderHook(
    'head.end',
    fn () => '<style>
        .fi-ta-table {
            border-collapse: collapse; /* Quita espacio entre celdas */
        }
        .fi-ta-table tr > td,
        .fi-ta-table tr > th {
            padding-top: 0rem !important;
            padding-bottom: 0rem !important;
            line-height: 0 !important;
            font-size: 8px !important;
        }
    </style>'
);
        });
    }
}
