<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Categoria' => 'App\Policies\CategoriaPolicy',
        'App\Models\Producto' => 'App\Policies\ProductoPolicy',
        'App\Models\Orden' => 'App\Policies\OrdenPolicy',
        'App\Models\Boucher' => 'App\Policies\BoucherPolicy',
        'App\Models\User' => 'App\Policies\UserPolicy',
        'App\Models\Dashboard' => 'App\Policies\DashboardPolicy',
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
