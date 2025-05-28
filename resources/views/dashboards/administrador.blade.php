@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Panel de Administrador</h1>
    <p>Bienvenido al panel de administración. Desde aquí puedes gestionar usuarios y ver estadísticas del sistema.</p>

    <div class="list-group mt-3 mb-4">
        <a href="{{ route('usuarios.index') }}" class="list-group-item list-group-item-action">
            <i class="bi bi-people-fill"></i> Administrar Usuarios
        </a>
        <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action">
            <i class="bi bi-box-seam"></i> Gestionar Productos
        </a>
        <a href="{{ route('categories.index') }}" class="list-group-item list-group-item-action">
            <i class="bi bi-tags-fill"></i> Gestionar Categorías
        </a>
        <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action">
            <i class="bi bi-receipt"></i> Ver Órdenes
        </a>
        {{-- Puedes añadir más enlaces a otras secciones administrativas --}}
    </div>

    <hr>
    <h2>Estadísticas Generales del Sistema</h2>

    <div class="row mt-3">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">Usuarios</div>
                <div class="card-body">
                    <p class="card-text"><strong>Usuarios Registrados Totales:</strong> {{ $totalUsers ?? 'N/A' }}</p>
                    <p class="card-text"><strong>Vendedores Activos:</strong> {{ $totalSellers ?? 'N/A' }}</p>
                    <p class="card-text"><strong>Compradores Activos:</strong> {{ $totalBuyers ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">Productos y Categorías</div>
                <div class="card-body">
                    <h5>Productos por Categoría:</h5>
                    @if(isset($productsPerCategory) && $productsPerCategory->isNotEmpty())
                    <ul>
                        @foreach($productsPerCategory as $categoryName => $count)
                        <li>{{ $categoryName }}: {{ $count }} producto(s)</li>
                        @endforeach
                    </ul>
                    @else
                    <p>No hay datos de categorías.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header">Rendimiento de Ventas</div>
                <div class="card-body">
                    <p class="card-text"><strong>Producto Más Vendido:</strong> {{ $mostSoldProduct ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Compradores Frecuentes por Categoría</div>
                <div class="card-body">
                    @if(isset($categoryTopBuyers) && !empty($categoryTopBuyers))
                    <ul>
                        @foreach($categoryTopBuyers as $categoryName => $buyerInfo)
                        <li><strong>{{ $categoryName }}:</strong> {{ $buyerInfo }}</li>
                        @endforeach
                    </ul>
                    @else
                    <p>No hay datos suficientes para determinar compradores frecuentes por categoría.</p>
                    @endif
                    <p class="mt-2 fst-italic"><small>Nota: La determinación del comprador más frecuente por categoría se realiza analizando las órdenes de los productos dentro de cada categoría.</small></p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection