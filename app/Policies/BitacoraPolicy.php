<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Bitacora;
use Illuminate\Auth\Access\HandlesAuthorization;

class BitacoraPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_bitacora');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bitacora $bitacora): bool
    {
        return $user->can('view_bitacora');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_bitacora');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bitacora $bitacora): bool
    {
        return $user->can('update_bitacora');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bitacora $bitacora): bool
    {
        return $user->can('delete_bitacora');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_bitacora');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Bitacora $bitacora): bool
    {
        return $user->can('force_delete_bitacora');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_bitacora');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Bitacora $bitacora): bool
    {
        return $user->can('restore_bitacora');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_bitacora');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Bitacora $bitacora): bool
    {
        return $user->can('replicate_bitacora');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_bitacora');
    }
}
