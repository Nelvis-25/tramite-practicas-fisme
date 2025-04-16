<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LineaInvestigacion;
use Illuminate\Auth\Access\HandlesAuthorization;

class LineaInvestigacionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_linea::investigacion');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LineaInvestigacion $lineaInvestigacion): bool
    {
        return $user->can('view_linea::investigacion');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_linea::investigacion');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LineaInvestigacion $lineaInvestigacion): bool
    {
        return $user->can('update_linea::investigacion');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LineaInvestigacion $lineaInvestigacion): bool
    {
        return $user->can('delete_linea::investigacion');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_linea::investigacion');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, LineaInvestigacion $lineaInvestigacion): bool
    {
        return $user->can('force_delete_linea::investigacion');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_linea::investigacion');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LineaInvestigacion $lineaInvestigacion): bool
    {
        return $user->can('restore_linea::investigacion');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_linea::investigacion');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, LineaInvestigacion $lineaInvestigacion): bool
    {
        return $user->can('replicate_linea::investigacion');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_linea::investigacion');
    }
}
