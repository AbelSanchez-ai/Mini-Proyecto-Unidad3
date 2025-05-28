<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\User;
use App\Http\Requests\StoreOrdenRequest;
use App\Http\Requests\UpdateOrdenRequest;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\VentaValidadaVendedorMail;
use App\Mail\CompraValidadaCompradorMail;

class OrdenController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(500, 'Error de autenticación.');
        }

        if ($user->role === 'administrador' || $user->role === 'gerente') {
            $orders = Orden::with('buyer')->latest()->paginate(15);
        } else {
            $orders = Orden::where('buyer_id', $user->id)->latest()->paginate(15);
        }
        return view('orders.index', compact('orders')); // 'orders' (plural) se mantiene
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cartItems = session()->get('cart', []);
        $products = [];

        if (count($cartItems) > 0) {
            $productIds = array_keys($cartItems);
            $products = Producto::whereIn('id', $productIds)->get();
        }
        return view('orders.create', compact('cartItems', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrdenRequest $request)
    {
        DB::beginTransaction();
        try {
            $cartItems = session()->get('cart', []);
            if (empty($cartItems)) {
                return redirect()->back()->with('error', 'Tu carrito está vacío');
            }

            $orderData = $request->validated();
            $orderData['buyer_id'] = Auth::id();
            $orderData['ticket_path'] = null;

            $order = Orden::create($orderData); // Ya usaba $order, lo cual es bueno

            $total = 0;
            $productIds = array_keys($cartItems);
            $productsInCart = Producto::whereIn('id', $productIds)->get()->keyBy('id');

            $productosParaTicket = [];

            foreach ($cartItems as $productId => $item) {
                $product = $productsInCart->get($productId);
                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "Producto con ID {$productId} no encontrado.");
                }
                $quantity = $item['quantity'];
                if ($product->stock < $quantity) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "No hay suficiente stock para {$product->name}");
                }
                $product->decrement('stock', $quantity);
                $order->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'price' => $product->price
                ]);
                $total += $product->price * $quantity;
                $productosParaTicket[] = ['product' => $product, 'quantity' => $quantity];
            }

            $order->total_amount = $total;

            if (!empty($productosParaTicket)) {
                // Pasar $order a generarTicketImagenGD
                $ticketPath = $this->generarTicketImagenGD($order, $productosParaTicket[0]['product'], $productosParaTicket[0]['quantity'], count($productosParaTicket) > 1);
                if ($ticketPath) {
                    $order->ticket_path = $ticketPath;
                }
            }

            $order->save();

            session()->forget('cart');
            DB::commit();
            // Pasar $order a la ruta
            return redirect()->route('orders.show', $order)->with('success', 'Orden creada exitosamente. Se ha generado su recibo.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar orden. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Orden $order) // Cambiado $orden a $order
    {
        $this->authorize('view', $order); // Cambiado $orden a $order
        $order->load('products', 'buyer'); // Cambiado $orden a $order
        return view('orders.show', compact('order')); // Cambiado 'orden' a 'order'
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orden $order) // Cambiado $orden a $order
    {
        $this->authorize('update', $order); // Cambiado $orden a $order
        $statuses = ['pending', 'processing', 'completed', 'cancelled', 'validated'];
        return view('orders.edit', compact('order', 'statuses')); // Cambiado 'orden' a 'order'
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrdenRequest $request, Orden $order)
    {
        $this->authorize('update', $order);
        $authUser = Auth::user(); // Renombrado para evitar conflicto con $user

        if (!$authUser) {
            abort(500, 'Error de autenticación.');
        }

        $allowedStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        if ($authUser->role === 'gerente' || $authUser->role === 'administrador') {
            $allowedStatuses[] = 'validated';
        }

        $validatedData = $request->validate([
            'status' => 'required|in:' . implode(',', $allowedStatuses)
        ]);

        $oldStatus = $order->status;
        $newStatus = $validatedData['status'];

        if ($newStatus === 'validated') {
            if ($authUser->role !== 'gerente' && $authUser->role !== 'administrador') {
                return redirect()->route('orders.show', $order)
                    ->with('error', 'No tienes permiso para validar esta orden.');
            }
        }

        $order->update($validatedData);

        // --- LÓGICA DE NOTIFICACIÓN POR CORREO ---
        if ($newStatus === 'validated' && $oldStatus !== 'validated') {
            // Cargar relaciones necesarias para los correos
            $order->load(['products.seller', 'buyer']);

            // 1. Notificación al Comprador
            if ($order->buyer && $order->buyer->email) {
                $vendedoresDeLaOrden = collect();
                foreach ($order->products as $productoEnOrden) {
                    if ($productoEnOrden->seller) { // Asumiendo que Producto tiene una relación 'vendedor'
                        $vendedoresDeLaOrden->push($productoEnOrden->seller);
                    }
                }
                $vendedoresUnicos = $vendedoresDeLaOrden->unique('id');

                if ($vendedoresUnicos->isNotEmpty()) {
                    try {
                        Mail::to($order->buyer->email)->send(new CompraValidadaCompradorMail($order, $vendedoresUnicos));
                    } catch (\Exception $e) {
                        // Opcional: Loggear el error si el correo falla
                        // Log::error("Error enviando email 'CompraValidadaCompradorMail' a {$order->buyer->email}: " . $e->getMessage());
                    }
                }
            }

            // 2. Notificación a cada Vendedor
            foreach ($order->products as $productoEnOrden) {
                // Asumiendo que Producto tiene una relación 'vendedor' que devuelve el User vendedor
                // y que la tabla pivote se accede mediante $productoEnOrden->pivot->quantity
                if ($productoEnOrden->seller && $productoEnOrden->seller->email && $order->buyer) {
                    $vendedor = $productoEnOrden->seller;
                    $cantidadVendida = $productoEnOrden->pivot->quantity;

                    try {
                        Mail::to($vendedor->email)->send(new VentaValidadaVendedorMail($order, $productoEnOrden, $cantidadVendida, $order->buyer));
                    } catch (\Exception $e) {
                        // Opcional: Loggear el error si el correo falla
                        // Log::error("Error enviando email 'VentaValidadaVendedorMail' a {$vendedor->email} para producto {$productoEnOrden->id}: " . $e->getMessage());
                    }
                }
            }
        }
        // --- FIN DE LÓGICA DE NOTIFICACIÓN ---

        return redirect()->route('orders.show', $order)->with('success', 'Estado de orden actualizado y notificaciones enviadas (si aplica).');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Orden $order) // Cambiado $orden a $order
    {
        $this->authorize('delete', $order); // Cambiado $orden a $order
        try {
            if ($order->ticket_path && Storage::disk('tickets_private')->exists($order->ticket_path)) { // Cambiado $orden a $order
                Storage::disk('tickets_private')->delete($order->ticket_path); // Cambiado $orden a $order
            }

            $order->products()->detach(); // Cambiado $orden a $order
            $order->delete(); // Cambiado $orden a $order
            return redirect()->route('orders.index')->with('success', 'Orden eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('orders.index')->with('error', 'No se pudo eliminar la orden.');
        }
    }

    /**
     * Procesa la compra directa de un producto.
     */
    public function buyNow(Request $request, Producto $product)
    {
        $user = Auth::user();
        $quantity = 1;

        if (!$user) {
            return back()->with('error', 'Debes iniciar sesión para comprar.');
        }

        if ($product->stock < $quantity) {
            return back()->with('error', 'Lo sentimos, este producto está agotado.');
        }

        DB::beginTransaction();

        try {
            // Usar $order consistentemente
            $order = Orden::create([
                'buyer_id' => $user->id,
                'total_amount' => $product->price * $quantity,
                'status' => 'processing',
                'ticket_path' => null,
            ]);

            $order->products()->attach($product->id, [
                'quantity' => $quantity,
                'price' => $product->price,
            ]);

            $product->decrement('stock', $quantity);

            // Pasar $order a generarTicketImagenGD
            $ticketPath = $this->generarTicketImagenGD($order, $product, $quantity);

            if ($ticketPath) {
                $order->ticket_path = $ticketPath;
                $order->save();
            }

            DB::commit();
            // Pasar $order a la ruta
            return redirect()->route('orders.show', $order)->with('success', '¡Compra realizada exitosamente! Se ha generado su recibo.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al procesar su compra. Por favor, inténtelo de nuevo.');
        }
    }

    /**
     * Genera una imagen de ticket/recibo usando GD.
     * @param Orden $order La orden para la cual generar el ticket. // Cambiado $orden a $order
     * @param Producto $producto El producto principal (para compra directa).
     * @param int $cantidad La cantidad del producto.
     * @param bool $isMultipleItems Indica si el ticket es para una orden con múltiples items (simplificado).
     * @return string|null La ruta del archivo del ticket guardado o null si falla.
     */
    private function generarTicketImagenGD(Orden $order, Producto $producto, int $cantidad, bool $isMultipleItems = false): ?string // Cambiado $orden a $order
    {
        /** @var \GdImage|resource|null $im */
        $im = null;

        try {
            $_im_temp = imagecreatetruecolor(800, 600);
            if ($_im_temp === false) {
                return null;
            }
            $im = $_im_temp;

            $blanco = imagecolorallocate($im, 255, 255, 255);
            $negro = imagecolorallocate($im, 0, 0, 0);
            $azul = imagecolorallocate($im, 70, 130, 180);
            $gris = imagecolorallocate($im, 200, 200, 200);
            $grisOscuro = imagecolorallocate($im, 100, 100, 100);

            if ($blanco === false || $negro === false || $azul === false || $gris === false || $grisOscuro === false) {
                return null;
            }

            imagefilledrectangle($im, 0, 0, 799, 599, $blanco);
            imagerectangle($im, 0, 0, 799, 599, $gris);

            $titulo = "COMPROBANTE DE COMPRA";
            $empresa = "TIENDA ELEKTRO";
            imagefilledrectangle($im, 0, 0, 799, 70, $azul);
            $fuenteGrande = 5;
            $fuenteNormal = 4;
            $fuentePequena = 3;

            imagestring($im, $fuenteGrande, (800 - imagefontwidth($fuenteGrande) * strlen($titulo)) / 2, 15, $titulo, $blanco);
            imagestring($im, $fuenteNormal, (800 - imagefontwidth($fuenteNormal) * strlen($empresa)) / 2, 40, $empresa, $blanco);

            $yPos = 90;
            imagestring($im, $fuenteNormal, 30, $yPos, "ORDEN #" . $order->id, $negro); // Cambiado $orden a $order
            $yPos += 20;
            imagestring($im, $fuenteNormal, 30, $yPos, "FECHA: " . $order->created_at->format('d/m/Y H:i:s'), $negro); // Cambiado $orden a $order
            $yPos += 20;
            imagestring($im, $fuenteNormal, 30, $yPos, "CLIENTE: " . ($order->buyer->name ?? Auth::user()->name ?? 'N/A'), $negro); // Cambiado $orden a $order
            $yPos += 30;

            imageline($im, 30, $yPos, 770, $yPos, $gris);
            $yPos += 15;

            imagestring($im, $fuenteNormal, 30, $yPos, "PRODUCTO", $grisOscuro);
            imagestring($im, $fuenteNormal, 400, $yPos, "PRECIO", $grisOscuro);
            imagestring($im, $fuenteNormal, 530, $yPos, "CANT.", $grisOscuro);
            imagestring($im, $fuenteNormal, 650, $yPos, "SUBTOTAL", $grisOscuro);
            $yPos += 20;
            imageline($im, 30, $yPos, 770, $yPos, $gris);
            $yPos += 15;

            if ($isMultipleItems) {
                imagestring($im, $fuenteNormal, 30, $yPos, "Varios productos (ver detalle de orden)", $negro);
                $yPos += 20;
            } else {
                $nombreProducto = strlen($producto->name) > 40 ? substr($producto->name, 0, 37) . '...' : $producto->name;
                imagestring($im, $fuenteNormal, 30, $yPos, $nombreProducto, $negro);
                imagestring($im, $fuenteNormal, 400, $yPos, "$" . number_format($producto->price, 2), $negro);
                imagestring($im, $fuenteNormal, 530, $yPos, (string)$cantidad, $negro);
                $subtotal = $producto->price * $cantidad;
                imagestring($im, $fuenteNormal, 650, $yPos, "$" . number_format($subtotal, 2), $negro);
                $yPos += 20;
            }
            $yPos += 10;

            imageline($im, 30, $yPos, 770, $yPos, $gris);
            $yPos += 20;

            imagestring($im, $fuenteGrande, 500, $yPos, "TOTAL:", $negro);
            imagestring($im, $fuenteGrande, 650, $yPos, "$" . number_format($order->total_amount, 2), $negro); // Cambiado $orden a $order
            $yPos += 50;

            $mensajePie = "Gracias por tu compra!";
            $notas = "* Este es un comprobante generado automaticamente.";
            $notas2 = "* Para cualquier aclaracion, conserve este comprobante.";

            imagestring($im, $fuenteNormal, (800 - imagefontwidth($fuenteNormal) * strlen($mensajePie)) / 2, $yPos, $mensajePie, $azul);
            $yPos += 30;
            imagestring($im, $fuentePequena, 30, $yPos, $notas, $negro);
            $yPos += 20;
            imagestring($im, $fuentePequena, 30, $yPos, $notas2, $negro);

            $filename = 'ticket_orden_' . $order->id . '_' . time() . '.png'; // Cambiado $orden a $order
            $fullPathToSave = Storage::disk('tickets_private')->path($filename);
            $directory = dirname($fullPathToSave);

            if (!is_dir($directory)) {
                if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
                    return null;
                }
            }

            if (!imagepng($im, $fullPathToSave)) {
                return null;
            }

            return $filename;
        } catch (\Exception $e) {
            return null;
        } finally {
            if ($im !== null) {
                imagedestroy($im);
            }
        }
    }

    /**
     * Muestra la imagen del ticket de una orden.
     */
    public function showTicketImage(Orden $order) // Cambiado $orden a $order
    {
        $this->authorize('view', $order); // Cambiado $orden a $order

        if (!empty($order->ticket_path) && Storage::disk('tickets_private')->exists($order->ticket_path)) { // Cambiado $orden a $order
            $fileContents = Storage::disk('tickets_private')->get($order->ticket_path); // Cambiado $orden a $order
            $mimeType = 'image/png';

            return Response::make($fileContents, 200, ['Content-Type' => $mimeType]);
        }
        abort(404, 'Recibo no encontrado.');
    }
}
