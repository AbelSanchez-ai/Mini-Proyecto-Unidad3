<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electrónica',
                'description' => 'Artículos electrónicos como teléfonos, computadoras y más',
            ],
            [
                'name' => 'Ropa',
                'description' => 'Todo tipo de prendas de vestir',
            ],
            [
                'name' => 'Hogar',
                'description' => 'Artículos para el hogar y la decoración',
            ],
            [
                'name' => 'Libros',
                'description' => 'Libros de diversos géneros',
            ],
            [
                'name' => 'Deportes',
                'description' => 'Equipamiento deportivo y accesorios',
            ],
            [
                'name' => 'Ofertas', // Ya está incluida
                'description' => 'Productos con descuentos especiales',
            ],
            // Puedes añadir más si quieres, pero ya cumple el mínimo de 5 + "Ofertas"
        ];

        foreach ($categories as $category) {
            // Usar updateOrCreate para evitar duplicados si se ejecuta el seeder múltiples veces
            Categoria::updateOrCreate(['name' => $category['name']], $category);
        }
        $this->command->info(count($categories) . ' categorías procesadas/creadas.');
    }
}
