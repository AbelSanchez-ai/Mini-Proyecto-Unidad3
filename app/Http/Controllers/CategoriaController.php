<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Si aquí usas 'categories' para la colección, está bien.
        $categories = Categoria::withCount('products')->get();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Categoria::class);
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoriaRequest $request)
    {
        $this->authorize('create', Categoria::class);
        Categoria::create($request->validated());
        return redirect()->route('categories.index')
            ->with('success', 'Categoría creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    // Cambia $categoria a $category aquí
    public function show(Categoria $category)
    {
        $products = $category->products()->paginate(12);
        // Y aquí en compact()
        return view('categories.show', compact('category', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    // Cambia $categoria a $category aquí
    public function edit(Categoria $category)
    {
        $this->authorize('update', $category);
        // Y aquí en compact()
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    // Cambia $categoria a $category aquí
    public function update(UpdateCategoriaRequest $request, Categoria $category)
    {
        $this->authorize('update', $category);
        $category->update($request->validated());
        return redirect()->route('categories.index')
            ->with('success', 'Categoría actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    // Cambia $categoria a $category aquí
    public function destroy(Categoria $category)
    {
        $this->authorize('delete', $category);
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'No se puede eliminar una categoría con productos asociados');
        }

        $category->delete();
        return redirect()->route('categories.index')
            ->with('success', 'Categoría eliminada exitosamente');
    }
}
