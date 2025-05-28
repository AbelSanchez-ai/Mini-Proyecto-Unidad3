<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Administrador
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Cambia 'password' por una contraseña segura
            'role' => 'administrador',
        ]);

        // Crear Gerente
        User::factory()->create([
            'name' => 'Gerente User',
            'email' => 'gerente@example.com',
            'password' => Hash::make('password'), // Cambia 'password' por una contraseña segura
            'role' => 'gerente',
        ]);

        // Crear 100 Clientes
        User::factory()->count(100)->create(['role' => 'cliente']);

        $this->command->info('User seeder finished: Admin, Gerente, and 100 Clientes created.');
    }
}
