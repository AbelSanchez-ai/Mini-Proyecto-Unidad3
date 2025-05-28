<?php

namespace App\Policies;

use App\Models\Boucher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BoucherPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Solo el gerente puede ver todos los bouchers
        return $user->role === 'gerente';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Boucher $boucher): bool
    {
        // Solo el dueño de la venta o el gerente puede visualizar el ticket
        return $user->isGerente() || $user->id === $boucher->orden->buyer_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isClient() || $user->isGerente();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Boucher $boucher): bool
    {
        return $user->isGerente();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Boucher $boucher): bool
    {
        return $user->isAdmin();
    }
}
