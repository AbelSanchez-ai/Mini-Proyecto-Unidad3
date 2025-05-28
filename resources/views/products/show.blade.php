@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Verifica si $product existe --}}
    @if($product)
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $product->name }}</h1>
            <p class="text-muted">
                Vendido por:
                @if($product->seller)
                {{ $product->seller->name }}
                @else
                Vendedor Desconocido
                @endif
            </p>

            @if($product->images->isNotEmpty())
            <div id="productImageCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    @foreach($product->images as $key => $image)
                    <button type="button" data-bs-target="#productImageCarousel" data-bs-slide-to="{{ $key }}" class="{{ $image->is_primary || ($key == 0 && !$product->images->contains('is_primary', true)) ? 'active' : '' }}" aria-current="{{ $image->is_primary || ($key == 0 && !$product->images->contains('is_primary', true)) ? 'true' : 'false' }}" aria-label="Slide {{ $key + 1 }}"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach($product->images as $key => $image)
                    <div class="carousel-item {{ $image->is_primary || ($key == 0 && !$product->images->contains('is_primary', true)) ? 'active' : '' }}">
                        <img src="{{ asset('storage/' . $image->path) }}" class="d-block w-100" alt="{{ $product->name }} - Imagen {{ $key + 1 }}" style="max-height: 500px; object-fit: contain;">
                    </div>
                    @endforeach
                </div>
                @if($product->images->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#productImageCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productImageCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
                @endif
            </div>
            @else
            <img src="{{ asset('images/no-image.png') }}" class="img-fluid mb-4" alt="Sin imagen" style="max-height: 400px; object-fit: contain;">
            @endif

            <h4>Descripción</h4>
            <p>{{ $product->description ?: 'No hay descripción disponible.' }}</p>

        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">${{ number_format($product->price, 2) }}</h2>
                    <p class="card-text">
                        <span class="badge bg-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                            {{ $product->stock > 0 ? 'En stock: '.$product->stock : 'Agotado' }}
                        </span>
                    </p>
                    <p class="card-text">
                        Estado: <span class="badge bg-{{ $product->status === 'active' ? 'info' : 'warning' }}">{{ ucfirst($product->status) }}</span>
                    </p>

                    <h5>Categorías:</h5>
                    <ul class="list-unstyled">
                        @forelse($product->categories as $category)
                        <li><span class="badge bg-secondary">{{ $category->name }}</span></li>
                        @empty
                        <li><small class="text-muted">Sin categorías asignadas.</small></li>
                        @endforelse
                    </ul>

                    <div class="d-grid gap-2">
                        {{-- Botón de Compra Directa --}}
                        @auth
                        {{-- Primero, verificar si el usuario tiene un rol que no puede comprar (admin/gerente) --}}
                        @if(auth()->user()->role !== 'administrador' && auth()->user()->role !== 'gerente')
                        @if($product->stock > 0 && $product->status === 'active')
                        {{-- Luego, asegurarse que el vendedor no pueda comprar su propio producto --}}
                        @if(auth()->id() !== $product->seller_id)
                        <form action="{{ route('products.buyNow', $product) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-lightning-charge-fill"></i> Comprar Ahora
                            </button>
                        </form>
                        @else
                        <button type="button" class="btn btn-outline-secondary w-100" disabled>No puedes comprar tu propio producto</button>
                        @endif
                        @elseif($product->stock <= 0)
                            <button type="button" class="btn btn-danger w-100" disabled>Agotado</button>
                            @else
                            <button type="button" class="btn btn-warning w-100" disabled>No disponible ({{ ucfirst($product->status) }})</button>
                            @endif
                            @else
                            {{-- Mensaje o botón deshabilitado para admin/gerente --}}
                            <button type="button" class="btn btn-outline-secondary w-100" disabled>Acción de compra no disponible para tu rol</button>
                            @endif
                            @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">Inicia sesión para comprar</a>
                            @endauth
                            {{-- Fin Botón de Compra Directa --}}

                            {{-- Aquí podrías añadir un botón de "Añadir al carrito" si tienes esa funcionalidad --}}
                            {{-- <button class="btn btn-success" type="button">Añadir al Carrito</button> --}}
                    </div>

                    @auth
                    @if(auth()->id() === $product->seller_id || (auth()->user() && auth()->user()->isAdmin()) )
                    <hr>
                    <h5>Acciones del Vendedor/Admin</h5>
                    <div class="d-grid gap-2">
                        {{-- Ahora $product coincide con el parámetro de ruta esperado 'product' --}}
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil"></i> Editar Producto
                        </a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este producto?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash"></i> Eliminar Producto
                            </button>
                        </form>
                    </div>
                    @endif
                    @endauth
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('products.index') }}" class="btn btn-light w-100">
                    <i class="bi bi-arrow-left"></i> Volver a la lista de productos
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-danger" role="alert">
        Producto no encontrado.
    </div>
    @endif
</div>
@endsection