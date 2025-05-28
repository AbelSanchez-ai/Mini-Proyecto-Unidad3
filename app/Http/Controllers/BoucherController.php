<?php

namespace App\Http\Controllers;

use App\Models\Boucher;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BoucherController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Boucher::class);

        $bouchers = Boucher::with('orden')->get();
        return view('bouchers.index', compact('bouchers'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Boucher $boucher)
    {
        $this->authorize('view', $boucher);

        return view('bouchers.show', compact('boucher'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Boucher::class);

        return view('bouchers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Boucher::class);

        // Validación
        $validated = $request->validate([
            'orden_id' => 'required|exists:ordens,id',
            // Otros campos según tu modelo
        ]);

        $boucher = Boucher::create($validated);

        return redirect()->route('bouchers.show', $boucher)
            ->with('success', 'Comprobante creado exitosamente');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Boucher $boucher)
    {
        $this->authorize('update', $boucher);

        return view('bouchers.edit', compact('boucher'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Boucher $boucher)
    {
        $this->authorize('update', $boucher);

        // Validación
        $validated = $request->validate([
            // Campos según tu modelo
        ]);

        $boucher->update($validated);

        return redirect()->route('bouchers.show', $boucher)
            ->with('success', 'Comprobante actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Boucher $boucher)
    {
        $this->authorize('delete', $boucher);

        $boucher->delete();

        return redirect()->route('bouchers.index')
            ->with('success', 'Comprobante eliminado exitosamente');
    }

    /**
     * Download the boucher as PDF.
     */
    public function download(Boucher $boucher)
    {
        $this->authorize('view', $boucher);

        // Lógica para generar y descargar el PDF
        // Puedes usar paquetes como barryvdh/laravel-dompdf

        // Ejemplo (requiere instalación del paquete):
        // $pdf = PDF::loadView('bouchers.pdf', compact('boucher'));
        // return $pdf->download('comprobante-'.$boucher->id.'.pdf');

        // Implementación temporal:
        return back()->with('info', 'La funcionalidad de descarga de PDFs aún no está implementada');
    }
}
