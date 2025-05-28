@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-8">
            <h1>{{ $category->name }}</h1> {{-- Usar $category --}}
            <p class="text-muted">{{ $category->description ?: 'Esta categoría no tiene una descripción detallada.' }}</p> {{-- Usar $category --}}
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left"></i> Volver a Categorías
            </a>
            @can('update', $category) {{-- Usar $category --}}
            <a href="{{ route('categories.edit', ['category' => $category]) }}" class="btn btn-secondary mb-2">
                <i class="bi bi-pencil"></i> Editar Categoría
            </a>
            @endcan
        </div>
    </div>

    <hr>

    <h3>Productos en esta Categoría ({{ $products->total() }})</h3>

    @if($products->isEmpty())
    <div class="alert alert-info mt-3">
        No hay productos asociados a esta categoría actualmente.
    </div>
    @else
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mt-3">
        @foreach($products as $product)
        <div class="col">
            <div class="card h-100">
                <div class="position-relative">
                    @if($product->images->isNotEmpty())
                    @php
                    $primaryImage = $product->images->where('is_primary', true)->first()
                    ?? $product->images->first();
                    @endphp
                    <img src="{{ asset('storage/' . $primaryImage->path) }}"
                        class="card-img-top"
                        alt="{{ $product->name }}"
                        style="height: 200px; object-fit: cover;">
                    @else
                    <img src="{{ asset('images/no-image.png') }}"
                        class="card-img-top"
                        alt="Sin imagen"
                        style="height: 200px; object-fit: cover;">
                    @endif
                    @if($product->status === 'inactive')
                    <span class="position-absolute top-0 end-0 badge bg-danger m-2">Inactivo</span>
                    @endif
                </div>
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                    </h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold fs-5">${{ number_format($product->price, 2) }}</span>
                        <span class="badge bg-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                            {{ $product->stock > 0 ? 'Stock: '.$product->stock : 'Agotado' }}
                        </span>
                    </div>
                    <p class="card-text text-muted">
                        <small>Vendido por: {{ $product->seller->name }}</small>
                    </p>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <a href="{{ route('products.show', $product) }}" class="btn btn-primary btn-sm w-100">
                        Ver detalles
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection