@extends('layouts.app') {{-- O tu layout principal/admin --}}

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1>Listado de Órdenes</h1>
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

    @if($orders->isEmpty())
    <div class="alert alert-info">
        No hay órdenes para mostrar.
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID Orden</th>
                    <th>Comprador</th>
                    <th>Email Comprador</th>
                    <th>Monto Total</th>
                    <th>Estado</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->buyer->name ?? 'N/A' }}</td>
                    <td>{{ $order->buyer->email ?? 'N/A' }}</td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        <span class="badge 
                                    @switch($order->status)
                                        @case('pending') bg-warning text-dark @break
                                        @case('processing') bg-info text-dark @break
                                        @case('completed') bg-success @break
                                        @case('cancelled') bg-danger @break
                                        @case('validated') bg-primary @break
                                        @default bg-secondary @break
                                    @endswitch">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info" title="Ver Detalles">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        @can('update', $order) {{-- Asumiendo que tienes una policy OrdenPolicy --}}
                        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-sm btn-warning" title="Editar Estado">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        @endcan
                        @can('delete', $order) {{-- Asumiendo que tienes una policy OrdenPolicy --}}
                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta orden?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Orden">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $orders->links() }} {{-- Paginación --}}
    </div>
    @endif
</div>
@endsection

{{-- Si usas FontAwesome para los iconos, asegúrate de tenerlo en tu layout --}}
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" /> --}}