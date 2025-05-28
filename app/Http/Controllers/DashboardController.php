<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// Asegúrate de importar los modelos necesarios
use App\Models\User;
use App\Models\Producto;
use App\Models\Categoria;
// Si tienes un modelo Orden, también podría ser necesario para otras estadísticas o roles
// use App\Models\Orden; 

class DashboardController extends Controller
{
    use AuthorizesRequests; // Si usas policies para el dashboard general

    public function index()
    {
        $user = Auth::user();
        $role = $user->role; // Asumiendo que el modelo User tiene un atributo 'role'

        if ($role === 'administrador') {
            // Lógica para el dashboard del Administrador con estadísticas

            // 1. ¿Cuántos usuarios hay registrados en total?
            $totalUsers = User::count();

            // 2. ¿Cuántos vendedores existen? (Usuarios con al menos un producto)
            $totalSellers = User::has('products')->count();

            // 3. ¿Cuántos compradores existen? (Usuarios con al menos una orden como comprador)
            $totalBuyers = User::has('orders')->count(); // Asegúrate que User tiene la relación orders()

            // 4. ¿Cuántos productos hay por cada categoría?
            $productsPerCategoryData = Categoria::withCount('products')->get();
            $productsPerCategory = $productsPerCategoryData->mapWithKeys(function ($category) {
                return [$category->name => $category->products_count];
            });

            // 5. ¿Cuál es el producto más vendido? (Por cantidad total en la tabla pivote 'order_items')
            $allProducts = Producto::with('orders')->get(); // 'orders' es la relación BelongsToMany con Orden via order_items
            $productSalesQuantities = collect();

            foreach ($allProducts as $product) {
                $totalQuantitySold = 0;
                // La relación 'orders' en Producto ya carga la tabla pivote 'order_items'
                // y 'pivot' contiene los datos de esa tabla (quantity, price)
                foreach ($product->orders as $order) { // Asumiendo que Producto tiene la relación orders()
                    if (isset($order->pivot->quantity)) { // Verificar que la cantidad existe en el pivot
                        $totalQuantitySold += $order->pivot->quantity;
                    }
                }
                if ($totalQuantitySold > 0) {
                    $productSalesQuantities->push(['id' => $product->id, 'name' => $product->name, 'total_sold' => $totalQuantitySold]);
                }
            }
            $mostSoldProductEntry = $productSalesQuantities->sortByDesc('total_sold')->first();
            $mostSoldProduct = $mostSoldProductEntry ? $mostSoldProductEntry['name'] . ' (' . $mostSoldProductEntry['total_sold'] . ' unidades)' : 'N/A';

            // 6. ¿Cuál es el nombre del comprador más frecuente por categoría?
            $categoriesDataForTopBuyers = Categoria::with(['products.orders.buyer'])->get();
            $categoryTopBuyers = [];

            foreach ($categoriesDataForTopBuyers as $category) {
                $buyersInThisCategory = collect();

                foreach ($category->products as $product) {
                    foreach ($product->orders as $order) {
                        if ($order->buyer) {
                            $buyerId = $order->buyer->id;
                            $buyerName = $order->buyer->name;

                            if (!$buyersInThisCategory->has($buyerId)) {
                                $buyersInThisCategory->put($buyerId, ['name' => $buyerName, 'count' => 0]);
                            }
                            $buyerData = $buyersInThisCategory->get($buyerId);
                            $buyerData['count']++;
                            $buyersInThisCategory->put($buyerId, $buyerData);
                        }
                    }
                }

                if ($buyersInThisCategory->isNotEmpty()) {
                    $topBuyerInCategory = $buyersInThisCategory->sortByDesc('count')->first();
                    $categoryTopBuyers[$category->name] = $topBuyerInCategory['name'] . ' (' . $topBuyerInCategory['count'] . ' compras)';
                } else {
                    $categoryTopBuyers[$category->name] = 'N/A (Sin ventas o compradores)';
                }
            }

            // Asegúrate de que la vista 'dashboards.administrador' (o como se llame tu vista principal del admin)
            // esté preparada para recibir y mostrar estas variables.
            return view("dashboards.administrador", compact(
                'totalUsers',
                'totalSellers',
                'totalBuyers',
                'productsPerCategory',
                'mostSoldProduct',
                'categoryTopBuyers'
                // Puedes pasar también $user si la vista lo necesita
            ));
        } elseif ($role === 'gerente') {
            // Lógica para el dashboard del gerente
            return view("dashboards.gerente"); // Pasa las variables necesarias para el gerente
        } elseif ($role === 'cliente') {
            // Lógica para el dashboard del cliente
            return view("dashboards.cliente"); // Pasa las variables necesarias para el cliente
        }

        // Fallback o dashboard por defecto si el rol no coincide o no está definido
        // O redirigir a la home con un mensaje.
        return redirect()->route('home')->with('error', 'Rol de usuario no reconocido para el dashboard.');
    }

    // Si tienes otros métodos como generalStats, salesStats, etc., y ya no son necesarios
    // porque su lógica se ha movido a index(), puedes considerar eliminarlos o refactorizarlos.
}
