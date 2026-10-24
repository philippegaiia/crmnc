<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LabIngredient;
use App\Models\User;
use App\Services\CompanyAccessService;

class LabIngredientPolicy
{
    public function __construct(private CompanyAccessService $companyAccess) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LabIngredient $labIngredient): bool
    {
        return $this->companyAccess->canAccessCompanyId($user, $labIngredient->company_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->role !== UserRole::Evaluateur;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LabIngredient $labIngredient): bool
    {
        return $this->view($user, $labIngredient) && $user->role !== UserRole::Evaluateur;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LabIngredient $labIngredient): bool
    {
        return $this->update($user, $labIngredient);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LabIngredient $labIngredient): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LabIngredient $labIngredient): bool
    {
        return false;
    }
}
