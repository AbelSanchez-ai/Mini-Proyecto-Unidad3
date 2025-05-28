<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Categoria extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriaFactory> */
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relación muchos a muchos con productos
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'category_product', 'category_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * Relación "a través de" con órdenes - todas las órdenes que contienen productos de esta categoría
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(
            Orden::class,
            Producto::class,
            'category_id', // Clave externa en productos
            'id', // Clave primaria en órdenes
            'id', // Clave primaria en categorías
            'id' // Clave en productos que conecta con órdenes
        );
    }
}
