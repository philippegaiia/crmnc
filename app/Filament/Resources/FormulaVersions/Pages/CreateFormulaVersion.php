<?php

namespace App\Filament\Resources\FormulaVersions\Pages;

use App\Filament\Resources\FormulaVersions\FormulaVersionResource;
use App\Models\Formula;
use App\Models\User;
use App\Services\CompanyAccessService;
use App\Services\FormulaVersionCreationService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

class CreateFormulaVersion extends CreateRecord
{
    protected static string $resource = FormulaVersionResource::class;

    /**
     * Allocate the version number and draft status through the creation service.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            throw new AuthorizationException;
        }

        $formula = app(CompanyAccessService::class)
            ->scopeToUserCompany(Formula::query(), $user)
            ->whereKey($data['formula_id'])
            ->firstOrFail();

        return app(FormulaVersionCreationService::class)->createDraft(
            $formula,
            $user,
            versionNote: $data['version_note'] ?? null,
        );
    }
}
