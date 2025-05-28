@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1>Productos</h1>
        </div>
        <div class="col-md-6 text-md-end">
            @auth
            @if(auth()->user()->role === 'cliente')
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Crear Producto
            </a>
            @endif
            @endauth
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($products->isEmpty())
    <div class="alert alert-info" role="alert">
        No hay productos disponibles en este momento.
    </div>
    @else
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @foreach($products as $product)
        <div class="col">
            <div class="card h-100">
                <!-- Imagen principal del producto -->
                <div class="position-relative product-image-container">
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

                    <!-- Indicador de estado -->
                    @if($product->status === 'inactive')
                    <span class="position-absolute top-0 end-0 badge bg-danger m-2">
                        Inactivo
                    </span>
                    @endif
                </div>

                <div class="card-body">
                    <h5 class="card-title">{{ $product->name }}</h5>

                    <!-- Precio y stock -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold fs-5">${{ number_format($product->price, 2) }}</span>
                        <span class="badge bg-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                            {{ $product->stock > 0 ? 'Stock: ' . $product->stock : 'Agotado' }}
                        </span>
                    </div>

                    <!-- Descripción corta -->
                    <p class="card-text text-muted">
                        {{ \Illuminate\Support\Str::limit($product->description, 100) }}
                    </p>

                    <!-- Categorías -->
                    <div class="mb-2">
                        @foreach($product->categories as $category)
                        <span class="badge bg-secondary me-1">{{ $category->name }}</span>
                        @endforeach
                    </div>

                    <!-- Vendedor -->
                    <p class="card-text">
                        <small class="text-muted">Vendedor: {{ $product->seller->name }}</small>
                    </p>
                </div>

                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-primary btn-sm">
                            Ver detalles
                        </a>

                        @auth
                        @if(auth()->id() === $product->seller_id || auth()->user()->isAdmin())
                        <div class="btn-group">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este producto?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                        @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection