<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\IngredientRef;
use App\Models\User;

class IngredientRefPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->role !== UserRole::Adherent;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IngredientRef $ingredientRef): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->role->isAssociationManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IngredientRef $ingredientRef): bool
    {
        return $this->create($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IngredientRef $ingredientRef): bool
    {
        return $user->is_active && $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, IngredientRef $ingredientRef): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, IngredientRef $ingredientRef): bool
    {
        return false;
    }
}
