<?php

namespace App\Policies;

use App\Models\Orden;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrdenPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Orden $orden): bool
    {
        return $user->id === $orden->buyer_id || $user->isAdmin() || $user->isGerente();
    }

    /**
     * Determine whether the user can create models.
     * Modificado: Ni admin ni gerente pueden crear órdenes (comprar).
     */
    public function create(User $user): bool
    {
        return !$user->isAdmin() && !$user->isGerente();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Orden $orden): bool
    {
        return $user->isGerente();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Orden $orden): bool
    {
        return false; // O la lógica que necesites
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Orden $orden): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Orden $orden): bool
    {
        return false;
    }

    /**
     * Determine whether the user can approve the order.
     */
    public function approve(User $user, Orden $orden): bool
    {
        // Solo el gerente puede validar (aprobar) una venta
        return $user->isGerente();
    }
}
