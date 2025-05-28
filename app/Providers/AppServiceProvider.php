<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Asegúrate de importar Gate
use App\Models\Categoria;             // Importa el modelo Categoria
use App\Policies\CategoriaPolicy;     // Importa la CategoriaPolicy

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
        // Registrar la política para el modelo Categoria
        Gate::policy(Categoria::class, CategoriaPolicy::class);
    }
}
