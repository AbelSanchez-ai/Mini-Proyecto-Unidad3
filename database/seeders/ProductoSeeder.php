<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Categoria::all();
        if ($categories->isEmpty()) {
            $this->command->error('No hay categorías disponibles. Ejecuta CategoriaSeeder primero.');
            return;
        }

        // Obtener todos los usuarios con rol 'cliente'
        $clientUsers = User::where('role', 'cliente')->get();

        if ($clientUsers->count() < 30) {
            $this->command->warn('Se necesitan al menos 30 usuarios con rol "cliente" para asignar como vendedores. Actualmente hay ' . $clientUsers->count());
            // Opcionalmente, podrías crear más aquí si es necesario, o simplemente tomar los que haya.
            // Por ahora, tomaremos hasta 30 o los que haya si son menos.
        }

        // Seleccionar 30 usuarios (o los que haya si son menos de 30) para ser vendedores
        $sellers = $clientUsers->take(30);

        if ($sellers->isEmpty()) {
            $this->command->info('No hay usuarios "cliente" para asignar como vendedores. No se crearán productos.');
            return;
        }

        $this->command->info("Asignando productos a " . $sellers->count() . " vendedores...");

        foreach ($sellers as $seller) {
            // Cada vendedor tendrá entre 3 y 7 productos
            $numberOfProducts = rand(3, 7);
            Producto::factory()
                ->count($numberOfProducts)
                ->state(['seller_id' => $seller->id]) // Asignar el vendedor actual
                ->create(); // El factory se encargará de las categorías
        }

        $totalProductsCreated = Producto::count(); // Contar todos los productos, no solo los de este seeder
        $this->command->info("ProductoSeeder finalizado. Total de productos en la BD: " . $totalProductsCreated);
    }
}
