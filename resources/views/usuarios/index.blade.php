@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="float-left">Gestión de Usuarios</h2>
                    {{-- Solo el admin puede crear usuarios --}}
                    @if(Auth::user()->role === 'administrador')
                    <a href="{{ route('usuarios.create') }}" class="btn btn-primary float-right">Crear Usuario</a>
                    @endif
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

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    {{-- El botón Editar solo aparece si el usuario autenticado es gerente Y el usuario a editar es cliente --}}
                                    @if(Auth::user()->role === 'gerente' && $user->role === 'cliente')
                                    <a href="{{ route('usuarios.edit', $user->id) }}" class="btn btn-sm btn-primary">Editar</a>
                                    @else
                                    {{-- Para otros casos (admin, cliente viendo a otro cliente, gerente viendo a no cliente), no se muestra el botón de editar --}}
                                    <span class="text-muted">No editable</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection