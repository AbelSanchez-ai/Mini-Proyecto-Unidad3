<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::all();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:cliente,administrador,gerente',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Asegura que use Hash::make()
            'role' => $request->role,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $user)
    {
        // Verificar que solo se puedan editar usuarios con rol "cliente"
        if ($user->role !== 'cliente') {
            return redirect()->route('usuarios.index')
                ->with('error', 'Solo se pueden editar usuarios con rol de cliente');
        }

        return view('usuarios.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Verificar que solo se puedan editar usuarios con rol "cliente"
        if ($user->role !== 'cliente') {
            return redirect()->route('usuarios.index')
                ->with('error', 'Solo se pueden editar usuarios con rol de cliente');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Solo actualizar la contraseña si se ha proporcionado una nueva
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }
}
