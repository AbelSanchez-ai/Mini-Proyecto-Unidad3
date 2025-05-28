<?php

namespace Database\Factories;

use App\Models\Orden;
use App\Models\User;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orden>
 */
class OrdenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Intentar obtener un usuario 'cliente' que no sea vendedor.
        // Esta lógica es un poco compleja para un factory, se manejará mejor en el Seeder.
        // Por ahora, tomamos cualquier cliente. El Seeder refinará quiénes son los compradores.
        $buyer = User::where('role', 'cliente')->inRandomOrder()->first();

        // Si no hay clientes, crear uno para el factory (esto no debería pasar si UserSeeder corre primero)
        if (!$buyer) {
            $buyer = User::factory()->create(['role' => 'cliente']);
        }

        return [
            'buyer_id' => $buyer->id,
            'total_amount' => 0, // Se calculará después de añadir productos
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled', 'validated']),
            'ticket_path' => null,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Orden $orden) {
            $productosParaAdjuntar = Producto::inRandomOrder()
                ->where('stock', '>', 0) // Solo productos con stock
                ->where('status', 'active') // Solo productos activos
                ->take(rand(1, 5)) // Cada orden tendrá entre 1 y 5 productos diferentes
                ->get();

            if ($productosParaAdjuntar->isEmpty() && Producto::count() > 0) {
                // Si no se seleccionaron productos pero hay productos disponibles, tomar uno
                $productosParaAdjuntar = Producto::where('stock', '>', 0)
                    ->where('status', 'active')
                    ->take(1)->get();
            }


            $calculatedTotal = 0;

            foreach ($productosParaAdjuntar as $producto) {
                $quantity = rand(1, 3); // Cantidad de cada producto en la orden

                // Asegurarse de no pedir más de lo que hay en stock
                if ($producto->stock < $quantity) {
                    $quantity = $producto->stock;
                }

                if ($quantity > 0) {
                    $orden->products()->attach($producto->id, [
                        'quantity' => $quantity,
                        'price' => $producto->price, // Precio al momento de la compra
                    ]);
                    $calculatedTotal += $producto->price * $quantity;

                    // Opcional: decrementar stock aquí si el seeder debe simularlo
                    // $producto->decrement('stock', $quantity);
                }
            }

            if ($calculatedTotal > 0) {
                $orden->total_amount = $calculatedTotal;
                $orden->save(); // Guardar el total calculado
            } else {
                // Si no se pudieron añadir productos (ej. no hay stock), se podría eliminar la orden
                // o dejarla con total 0 y sin items, dependiendo de la lógica deseada.
                // Por ahora, la dejamos, pero podría ser un caso a manejar.
                // $orden->delete();
            }
        });
    }
}
