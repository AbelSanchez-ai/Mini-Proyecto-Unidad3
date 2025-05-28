<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Categoria; // Importar Categoria
use App\Models\Producto;  // Importar Producto
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(0, 100),
            // 'seller_id' se asignará en el Seeder
            'status' => fake()->randomElement(['active', 'inactive']), // Añadir variedad al estado
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Producto $producto) {
            $categories = Categoria::inRandomOrder()->take(rand(1, 3))->pluck('id');
            if ($categories->isNotEmpty()) {
                $producto->categories()->attach($categories);
            } elseif (Categoria::count() > 0) {
                // Si hay categorías pero por alguna razón no se seleccionaron, adjuntar la primera
                $producto->categories()->attach(Categoria::first()->id);
            }
            // Si no hay categorías en la BD, no se puede adjuntar.
            // El CategoriaSeeder debe ejecutarse primero.
        });
    }
}
