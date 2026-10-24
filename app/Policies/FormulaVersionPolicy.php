<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FormulaVersion;
use App\Models\User;
use App\Services\CompanyAccessService;

class FormulaVersionPolicy
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
    public function view(User $user, FormulaVersion $formulaVersion): bool
    {
        return $this->companyAccess->canAccessCompanyId($user, $formulaVersion->formula->company_id);
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
    public function update(User $user, FormulaVersion $formulaVersion): bool
    {
        return $this->view($user, $formulaVersion) && $user->role !== UserRole::Evaluateur;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FormulaVersion $formulaVersion): bool
    {
        return $this->update($user, $formulaVersion);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FormulaVersion $formulaVersion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FormulaVersion $formulaVersion): bool
    {
        return false;
    }
}
