@extends('layouts.app') {{-- O tu layout principal/admin --}}

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2>Editar Estado de la Orden #{{ $order->id }}</h2> {{-- Cambiado $orden a $order --}}
                </div>
                <div class="card-body">
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
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('orders.update', $order) }}" method="POST"> {{-- Cambiado $orden->id a $order --}}
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <p><strong>ID Orden:</strong> {{ $order->id }}</p> {{-- Cambiado $orden a $order --}}
                            <p><strong>Comprador:</strong> {{ $order->buyer->name ?? 'N/A' }} ({{ $order->buyer->email ?? 'N/A' }})</p> {{-- Cambiado $orden a $order --}}
                            <p><strong>Monto Total:</strong> ${{ number_format($order->total_amount, 2) }}</p> {{-- Cambiado $orden a $order --}}
                            <p><strong>Fecha de Creación:</strong> {{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A' }}</p> {{-- Cambiado $orden a $order (y añadido chequeo por si acaso) --}}
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Estado de la Orden</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach($statuses as $statusValue)
                                <option value="{{ $statusValue }}" {{ old('status', $order->status) == $statusValue ? 'selected' : '' }}> {{-- Cambiado $orden a $order --}}
                                    {{ ucfirst($statusValue) }}
                                </option>
                                @endforeach
                            </select>
                            @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">Cancelar</a> {{-- Cambiado $orden->id a $order --}}
                            <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection