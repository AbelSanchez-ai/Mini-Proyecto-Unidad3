@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Panel de Gerente</h1>
    <p>Aquí puedes administrar empleados, estadísticas y configuración general.</p>
    <a href="{{ route('usuarios.index') }}" class="btn btn-primary mb-2">Administrar Usuarios</a>
    <a href="{{ route('products.index') }}" class="btn btn-info mb-2">Administrar Productos</a>
    <a href="{{ route('orders.index') }}" class="btn btn-warning mb-2">Ver Órdenes</a>
</div>
@endsection