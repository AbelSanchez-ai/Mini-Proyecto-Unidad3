<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Orden extends Model
{
    /** @use HasFactory<\Database\Factories\OrdenFactory> */
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'buyer_id',
        'total_amount',
        'status',
        'ticket_path',
    ];

    /**
     * Relación con el comprador (usuario)
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Relación muchos a muchos con productos (a través de order_items)
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'order_items', 'order_id', 'product_id')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    /**
     * Calcular el total de la orden basado en items
     * Nota: Este método calcula el total, pero la orden ya tiene un 'total_amount'.
     * Podría usarse para verificar o si el total_amount no se almacena directamente.
     */
    public function calculateTotal()
    {
        return $this->products->sum(function ($product) {
            return $product->pivot->price * $product->pivot->quantity;
        });
    }
}
