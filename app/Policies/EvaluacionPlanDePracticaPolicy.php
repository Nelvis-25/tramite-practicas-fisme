<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EvaluacionPlanDePractica;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvaluacionPlanDePracticaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EvaluacionPlanDePractica $evaluacionPlanDePractica): bool
    {
        return $user->can('view_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EvaluacionPlanDePractica $evaluacionPlanDePractica): bool
    {
        return $user->can('update_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EvaluacionPlanDePractica $evaluacionPlanDePractica): bool
    {
        return $user->can('delete_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, EvaluacionPlanDePractica $evaluacionPlanDePractica): bool
    {
        return $user->can('force_delete_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, EvaluacionPlanDePractica $evaluacionPlanDePractica): bool
    {
        return $user->can('restore_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, EvaluacionPlanDePractica $evaluacionPlanDePractica): bool
    {
        return $user->can('replicate_evaluacion::plan::de::practica');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_evaluacion::plan::de::practica');
    }
}
