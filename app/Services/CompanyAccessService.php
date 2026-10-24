<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompanyAccessService
{
    public function canAccessCompany(User $user, Company $company): bool
    {
        return $this->canAccessCompanyId($user, $company->getKey());
    }

    public function canAccessCompanyId(User $user, int $companyId): bool
    {
        return $user->is_active && (
            $user->role !== UserRole::Adherent
            || $user->company_id === $companyId
        );
    }

    /**
     * Restrict a company query to the companies the user may access.
     *
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function scopeCompanies(Builder $query, ?Authenticatable $user): Builder
    {
        return $this->scopeToUserCompany($query, $user, $query->getModel()->getQualifiedKeyName());
    }

    /**
     * Restrict a company-owned query to the records the user may access.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function scopeToUserCompany(Builder $query, ?Authenticatable $user, string $companyColumn = 'company_id'): Builder
    {
        if (! $user instanceof User || ! $user->is_active) {
            return $query->whereKey(null);
        }

        if ($user->role === UserRole::Adherent) {
            return $query->where($companyColumn, $user->company_id);
        }

        return $query;
    }
}
