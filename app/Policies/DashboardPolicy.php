<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view general statistics.
     */
    public function viewGeneralStatistics(User $user): bool
    {
        // Solo el administrador puede ver el dashboard con estadísticas generales
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view sales statistics.
     */
    public function viewSalesStatistics(User $user): bool
    {
        // Gerentes pueden ver estadísticas de ventas
        return $user->isAdmin() || $user->isGerente();
    }

    /**
     * Determine whether the user can view personal statistics.
     */
    public function viewPersonalStatistics(User $user): bool
    {
        // Todos los usuarios pueden ver sus estadísticas personales
        return true;
    }
}
