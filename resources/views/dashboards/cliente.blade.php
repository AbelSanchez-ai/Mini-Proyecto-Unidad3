@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Bienvenido, Cliente</h1>
    <p>Aquí puedes ver tus pedidos, productos favoritos y más.</p>

    {{-- Botón para ver todos los productos --}}
    <a href="{{ route('products.index') }}" class="btn btn-primary">Ver productos</a>

    {{-- Botón para ver los pedidos del cliente --}}
    <a href="{{ route('orders.index') }}" class="btn btn-primary">Mis pedidos</a>
</div>
@endsection