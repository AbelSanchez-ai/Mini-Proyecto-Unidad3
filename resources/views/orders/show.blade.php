@extends('layouts.app') {{-- O tu layout principal/admin --}}

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>Detalles de la Orden #{{ $order->id }}</h2> {{-- Cambiado $orden a $order --}}
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            Información General
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID Orden:</strong> {{ $order->id }}</p> {{-- Cambiado $orden a $order --}}
                </div>
                <div class="col-md-6">
                    <p><strong>Comprador:</strong> {{ $order->buyer->name ?? 'N/A' }}</p> {{-- Cambiado $orden a $order --}}
                    <p><strong>Email Comprador:</strong> {{ $order->buyer->email ?? 'N/A' }}</p> {{-- Cambiado $orden a $order --}}
                    <p><strong>Estado:</strong>
                        <span class="badge 
                            @switch($order->status) {{-- Cambiado $orden a $order --}}
                                @case('pending') bg-warning text-dark @break
                                @case('processing') bg-info text-dark @break
                                @case('completed') bg-success @break
                                @case('cancelled') bg-danger @break
                                @case('validated') bg-primary @break
                                @default bg-secondary @break
                            @endswitch">
                            {{ ucfirst($order->status) }} {{-- Cambiado $orden a $order --}}
                        </span>
                    </p>
                    <p><strong>Monto Total:</strong> ${{ number_format($order->total_amount, 2) }}</p> {{-- Cambiado $orden a $order --}}
                </div>
            </div>
            {{-- Modificación para updated_at --}}
            <div class="row">
                <div class="col-md-12">
                    <p><strong>Fecha de Creación:</strong> {{ $order->created_at ? $order->created_at->format('d/m/Y H:i:s') : 'N/A' }}</p> {{-- Cambiado $orden a $order --}}
                    <p><strong>Última Actualización:</strong> {{ $order->updated_at ? $order->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</p> {{-- Cambiado $orden a $order --}}
                </div>
            </div>
            {{-- Fin de la modificación --}}
            @if($order->ticket_path) {{-- Cambiado $orden a $order --}}
            <div class="mt-3">
                <a href="{{ route('orders.showTicketImage', $order) }}" target="_blank" class="btn btn-info"> {{-- Cambiado $orden->id a $order --}}
                    <i class="fas fa-receipt"></i> Ver Recibo
                </a>
            </div>
            @else
            <p class="mt-3 text-muted">No hay recibo digital disponible para esta orden.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Productos en esta Orden
        </div>
        <div class="card-body">
            @if($order->products->isEmpty()) {{-- Cambiado $orden a $order --}}
            <p>No hay productos asociados a esta orden.</p>
            @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->products as $product) {{-- Cambiado $orden a $order --}}
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                            </td>
                            <td>{{ $product->pivot->quantity }}</td>
                            <td>${{ number_format($product->pivot->price, 2) }}</td>
                            <td>${{ number_format($product->pivot->quantity * $product->pivot->price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        @can('update', $order) {{-- Cambiado $orden a $order --}}
        <a href="{{ route('orders.edit', $order) }}" class="btn btn-warning"> {{-- Cambiado $orden->id a $order --}}
            <i class="fas fa-edit"></i> Editar Estado
        </a>
        @endcan
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-list"></i> Volver al Listado de Órdenes
        </a>
    </div>
</div>
@endsection

{{-- Si usas FontAwesome para los iconos, asegúrate de tenerlo en tu layout --}}
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" /> --}}