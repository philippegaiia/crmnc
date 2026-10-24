<?php

namespace App\Services;

use App\Enums\FormulaVersionStatus;
use App\Models\Formula;
use App\Models\FormulaVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FormulaVersionCreationService
{
    public function createDraft(Formula $formula, User $creator, ?string $versionNote = null): FormulaVersion
    {
        return DB::transaction(function () use ($formula, $creator, $versionNote): FormulaVersion {
            $lockedFormula = Formula::query()
                ->whereKey($formula->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $nextVersionNumber = ((int) $lockedFormula->versions()->max('version_number')) + 1;

            return $lockedFormula->versions()->create([
                'version_number' => $nextVersionNumber,
                'workflow_status' => FormulaVersionStatus::Brouillon,
                'created_by' => $creator->getKey(),
                'version_note' => $versionNote,
            ]);
        });
    }
}
