<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Validacion;
use Illuminate\Auth\Access\HandlesAuthorization;

class ValidacionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_validacion');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Validacion $validacion): bool
    {
        return $user->can('view_validacion');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_validacion');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Validacion $validacion): bool
    {
        return $user->can('update_validacion');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Validacion $validacion): bool
    {
        return $user->can('delete_validacion');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_validacion');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Validacion $validacion): bool
    {
        return $user->can('force_delete_validacion');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_validacion');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Validacion $validacion): bool
    {
        return $user->can('restore_validacion');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_validacion');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Validacion $validacion): bool
    {
        return $user->can('replicate_validacion');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_validacion');
    }
}
