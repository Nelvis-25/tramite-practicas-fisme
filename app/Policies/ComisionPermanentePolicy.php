<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ComisionPermanente;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComisionPermanentePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_comision::permanente');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ComisionPermanente $comisionPermanente): bool
    {
        return $user->can('view_comision::permanente');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_comision::permanente');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ComisionPermanente $comisionPermanente): bool
    {
        return $user->can('update_comision::permanente');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ComisionPermanente $comisionPermanente): bool
    {
        return $user->can('delete_comision::permanente');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_comision::permanente');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ComisionPermanente $comisionPermanente): bool
    {
        return $user->can('force_delete_comision::permanente');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_comision::permanente');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ComisionPermanente $comisionPermanente): bool
    {
        return $user->can('restore_comision::permanente');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_comision::permanente');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ComisionPermanente $comisionPermanente): bool
    {
        return $user->can('replicate_comision::permanente');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_comision::permanente');
    }
}
