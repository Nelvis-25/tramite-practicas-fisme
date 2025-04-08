<?php

namespace App\Providers;

use App\Http\Livewire\NotasSolicitud;
use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registra el componente Livewire
        Livewire::component('notas-solicitud', NotasSolicitud::class);
        
        // Elimina esto si no lo necesitas
        // $this->registerObservers();
    }

    /* Elimina este método si no lo usas
    protected function registerObservers(): void
    {
        // Configuración de observers si es necesaria
    }
    */
}