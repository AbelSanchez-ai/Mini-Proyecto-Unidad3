@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h1>Categorías</h1>
        </div>
        <div class="col-md-6 text-md-end">
            @can('create', App\Models\Categoria::class) {{-- O usa tu middleware 'admin' si lo prefieres --}}
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Categoría
            </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($categories->isEmpty())
    <div class="alert alert-info">
        No hay categorías registradas.
    </div>
    @else
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-center">Productos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>
                            <a href="{{ route('categories.show', $category) }}">{{ $category->name }}</a>
                        </td>
                        <td>{{ Str::limit($category->description, 80) ?: '-' }}</td>
                        <td class="text-center">{{ $category->products_count }}</td>
                        <td class="text-end">
                            <a href="{{ route('categories.show', $category) }}" class="btn btn-sm btn-outline-info" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            @can('update', $category) {{-- O usa tu middleware 'admin' --}}
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('delete', $category) {{-- O usa tu middleware 'admin' --}}
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar la categoría \'{{ $category->name }}\'? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection