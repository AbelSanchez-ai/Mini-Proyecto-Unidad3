<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\ProductoImagen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductoController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Producto::with(['seller', 'categories', 'images'])->get();
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Categoria::all();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request)
    {
        $data = $request->validated();
        $data['seller_id'] = Auth::id();

        $product = Producto::create($data);

        if ($request->has('categories')) {
            $product->categories()->attach($request->categories);
        }

        if ($request->hasFile('images')) {
            $this->processImages($product, $request->file('images'));
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $product)
    {
        $product->load(['categories', 'images', 'seller']);
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $product)
    {
        $this->authorize('update', $product);
        $categories = Categoria::all();
        $selectedCategories = $product->categories->pluck('id')->toArray();
        return view('products.edit', compact('product', 'categories', 'selectedCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $product)
    {
        $this->authorize('update', $product);

        $data = $request->validated();
        $product->update($data);

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        if ($request->hasFile('images')) {
            $this->processImages($product, $request->file('images'));
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $product)
    {
        $this->authorize('delete', $product);

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente');
    }

    /**
     * Procesar y guardar imágenes del producto
     */
    private function processImages(Producto $product, array $images)
    {
        $count = 0;
        foreach ($images as $image) {
            $path = $image->store('products', 'public');
            ProductoImagen::create([
                'product_id' => $product->id,
                'path' => $path,
                'is_primary' => $count === 0 && $product->images()->count() === 0,
                'order' => $product->images()->count() + $count + 1,
            ]);
            $count++;
        }
    }

    /**
     * Establecer una imagen como principal
     */
    public function setPrimaryImage(Producto $product, ProductoImagen $image)
    {
        $this->authorize('update', $product);
        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
        return back()->with('success', 'Imagen principal actualizada');
    }

    /**
     * Eliminar una imagen
     */
    public function deleteImage(Producto $product, ProductoImagen $image)
    {
        $this->authorize('update', $product);
        Storage::disk('public')->delete($image->path);
        $isPrimary = $image->is_primary;
        $image->delete();
        if ($isPrimary && $product->images()->count() > 0) {
            $product->images()->first()->update(['is_primary' => true]);
        }
        return back()->with('success', 'Imagen eliminada correctamente');
    }
}
