<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrdenController; // Asegúrate que OrdenController está importado
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoriaController;
use App\Models\User; // Importar el modelo User para el middleware 'can'

// Página principal -> Mostrará la página estática
Route::get('/', function () {
    return view('static'); // Muestra "Quiénes Somos"
})->name('home');

// Rutas protegidas con autenticación
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de usuarios - Protegidas con políticas
    Route::get('/usuarios', [UserController::class, 'index'])
        ->name('usuarios.index')
        ->middleware('can:viewAny,' . User::class);

    Route::get('/usuarios/create', [UserController::class, 'create'])
        ->name('usuarios.create')
        ->middleware('can:create,' . User::class);

    Route::post('/usuarios', [UserController::class, 'store'])
        ->name('usuarios.store')
        ->middleware('can:create,' . User::class); // O manejar con $this->authorize en el controlador

    Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])
        ->name('usuarios.edit')
        ->middleware('can:update,user'); // 'user' es el nombre del parámetro de ruta

    Route::put('/usuarios/{user}', [UserController::class, 'update'])
        ->name('usuarios.update')
        ->middleware('can:update,user'); // O manejar con $this->authorize en el controlador

    // Nota: La ruta de eliminación (destroy) para usuarios no está definida, si la añades, protégela también.
    // Ejemplo: Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy')->middleware('can:delete,user');


    // Productos
    Route::resource('products', ProductoController::class);

    // Rutas adicionales para manejar imágenes de productos
    Route::post('products/{product}/images/{image}/primary', [ProductoController::class, 'setPrimaryImage'])
        ->name('products.images.primary');
    Route::delete('products/{product}/images/{image}', [ProductoController::class, 'deleteImage'])
        ->name('products.images.delete');

    // Categorías
    Route::resource('categories', CategoriaController::class);
    // Se asume que CategoriaController usa $this->authorize() internamente para create, update, delete.

    // Órdenes
    Route::resource('orders', OrdenController::class);
    // Se asume que OrdenController usa $this->authorize() internamente.
    // Ruta para la imagen del ticket de la orden
    Route::get('/orders/{order}/ticket-image', [OrdenController::class, 'showTicketImage'])->name('orders.showTicketImage');
    // Ruta para aprobar/validar una orden (si la mantienes separada de update)
    Route::post('/orders/{order}/approve', [OrdenController::class, 'approve'])->name('orders.approve');


    // --- RUTA DE COMPRA DIRECTA ---
    Route::post('/products/{product}/buy', [OrdenController::class, 'buyNow'])->name('products.buyNow');
    // ---------------------------------------------------------

});

// Cargar autenticación
require __DIR__ . '/auth.php';
