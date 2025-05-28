<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    /** @use HasFactory<\Database\Factories\ProductoFactory> */
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'seller_id',
        'status',
    ];

    /**
     * Relación con el vendedor (usuario)
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Relación muchos a muchos con categorías
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'category_product', 'product_id', 'category_id')
            ->withTimestamps();
    }

    /**
     * Relación con imágenes del producto
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductoImagen::class, 'product_id');
    }

    /**
     * Obtener la imagen principal del producto
     */
    public function getPrimaryImageAttribute()
    {
        $primaryImage = $this->images()->where('is_primary', true)->first();

        if (!$primaryImage) {
            $primaryImage = $this->images()->first();
        }

        return $primaryImage;
    }

    /**
     * Obtener URL de la imagen principal
     */
    public function getThumbnailAttribute(): string
    {
        if ($this->primaryImage) {
            return $this->primaryImage->url;
        }

        return asset('images/default-product.png');
    }

    /**
     * Relación con órdenes (a través de order_items)
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Orden::class, 'order_items', 'product_id', 'order_id')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
