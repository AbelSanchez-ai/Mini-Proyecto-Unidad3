<?php

namespace Database\Seeders;

use App\Models\Orden;
use App\Models\User;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando OrdenSeeder...');

        // Obtener los IDs de los usuarios que son vendedores
        // Asumimos que ProductoSeeder ya asignó 'seller_id' a los productos
        $sellerIds = Producto::distinct('seller_id')->pluck('seller_id')->toArray();

        // Obtener usuarios 'cliente' que NO están en la lista de vendedores
        $buyers = User::where('role', 'cliente')
            ->whereNotIn('id', $sellerIds)
            ->get();

        $numberOfBuyers = $buyers->count();
        $this->command->info("Se encontraron {$numberOfBuyers} clientes compradores (no vendedores).");

        if ($numberOfBuyers === 0) {
            $this->command->warn('No hay clientes compradores disponibles para crear órdenes. Asegúrate de que UserSeeder y ProductoSeeder se hayan ejecutado y haya clientes que no sean vendedores.');
            return;
        }

        if (Producto::where('stock', '>', 0)->where('status', 'active')->count() === 0) {
            $this->command->warn('No hay productos activos con stock disponibles para crear órdenes. Asegúrate de que ProductoSeeder se haya ejecutado correctamente.');
            return;
        }

        $ordersCreatedCount = 0;
        foreach ($buyers as $buyer) {
            // Cada comprador tendrá entre 1 y 3 órdenes
            $numberOfOrders = rand(1, 3);
            for ($i = 0; $i < $numberOfOrders; $i++) {
                try {
                    Orden::factory()->create(['buyer_id' => $buyer->id]);
                    $ordersCreatedCount++;
                } catch (\Exception $e) {
                    $this->command->error("Error creando orden para el comprador ID {$buyer->id}: " . $e->getMessage());
                }
            }
        }

        $this->command->info("OrdenSeeder finalizado. Se crearon {$ordersCreatedCount} órdenes.");
    }
}
