<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,       // Crear usuarios primero (admin, gerente, clientes)
            CategoriaSeeder::class,  // Luego categorías
            ProductoSeeder::class,   // Productos, que dependen de usuarios (vendedores) y categorías
            OrdenSeeder::class,      // Órdenes, que dependen de usuarios (compradores) y productos
        ]);
    }
}
