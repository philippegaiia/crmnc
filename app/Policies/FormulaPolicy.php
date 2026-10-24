<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Formula;
use App\Models\User;
use App\Services\CompanyAccessService;

class FormulaPolicy
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
    public function view(User $user, Formula $formula): bool
    {
        return $this->companyAccess->canAccessCompanyId($user, $formula->company_id);
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
    public function update(User $user, Formula $formula): bool
    {
        return $this->view($user, $formula) && $user->role !== UserRole::Evaluateur;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Formula $formula): bool
    {
        return $this->update($user, $formula);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Formula $formula): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Formula $formula): bool
    {
        return false;
    }
}
