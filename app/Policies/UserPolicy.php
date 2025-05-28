<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isGerente();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Un usuario puede ver su propio perfil, o si es admin/gerente puede ver otros.
        return $user->id === $model->id || $user->isAdmin() || $user->isGerente();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo el administrador puede crear nuevos usuarios
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Modificado: Solo un gerente puede editar, y solo a usuarios con rol 'cliente'.
     * El administrador no puede editar usuarios a través de esta política/ruta.
     * El usuario autenticado es $user (primer parámetro), el usuario a editar es $model (segundo parámetro).
     */
    public function update(User $user, User $model): bool
    {
        return $user->isGerente() && $model->isClient();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Solo el admin puede eliminar y no a sí mismo.
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
