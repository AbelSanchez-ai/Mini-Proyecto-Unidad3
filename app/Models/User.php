<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Productos que el usuario vende (como vendedor)
     */
    public function products(): HasMany
    {
        return $this->hasMany(Producto::class, 'seller_id');
    }

    /**
     * Órdenes que el usuario ha realizado (como comprador)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Orden::class, 'buyer_id');
    }

    /**
     * Productos que el usuario ha comprado (a través de sus órdenes)
     */
    public function purchasedProducts(): HasManyThrough
    {
        return $this->hasManyThrough(
            Producto::class,
            Orden::class,
            'buyer_id', // Clave en órdenes que conecta con usuarios
            'id', // Clave en productos
            'id', // Clave en usuarios
            'id' // Clave en órdenes que conecta con productos (a través de order_items)
        );
    }

    /**
     * Comprobar si el usuario es cliente
     */
    public function isClient(): bool
    {
        return $this->role === 'cliente';
    }

    /**
     * Comprobar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'administrador';
    }

    /**
     * Comprobar si el usuario es gerente
     */
    public function isGerente(): bool
    {
        return $this->role === 'gerente';
    }
}
