@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registro</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="name">Nombre</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div>
            <label for="email">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" required>
        </div>

        <div>
            <label for="password_confirmation">Confirmar Contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>

        <!-- Campo para seleccionar el rol -->
        <div>
            <label for="role">Selecciona un Rol</label>
            <select name="role" id="role" required>
                <option value="cliente">Cliente</option>
                <option value="administrador">Administrador</option>
                <option value="gerente">Gerente</option>
            </select>
        </div>

        <button type="submit">Registrarse</button>
    </form>
</div>
@endsection