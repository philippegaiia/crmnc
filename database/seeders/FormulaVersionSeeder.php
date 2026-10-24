<?php

namespace Database\Seeders;

use App\Enums\FormulaVersionStatus;
use App\Models\Formula;
use App\Models\FormulaVersion;
use App\Models\User;
use Illuminate\Database\Seeder;

class FormulaVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $versions = [
            ['formula' => 'HYDRA-001', 'number' => 1, 'status' => FormulaVersionStatus::Soumis, 'note' => 'Première soumission : justificatif du conservateur à compléter.', 'creator' => 'adherent@mediterranee.test', 'submitted' => now()->subDays(24)],
            ['formula' => 'HYDRA-001', 'number' => 2, 'status' => FormulaVersionStatus::EnEtude, 'note' => 'Formule révisée avec ajout de niacinamide et ajustement du pH.', 'creator' => 'adherent@mediterranee.test', 'submitted' => now()->subDays(7)],
            ['formula' => 'DOUCHE-002', 'number' => 1, 'status' => FormulaVersionStatus::Incomplet, 'note' => 'Pourcentages des conservateurs et documentation tensioactive à confirmer.', 'creator' => 'adherent@mediterranee.test', 'submitted' => now()->subDays(12)],
            ['formula' => 'HUILE-001', 'number' => 1, 'status' => FormulaVersionStatus::Accepte, 'note' => 'Formule générique acceptée sous réserve des documents fournisseurs.', 'creator' => 'adherent@maison-botanique.test', 'submitted' => now()->subDays(35)],
            ['formula' => 'SHAMP-002', 'number' => 1, 'status' => FormulaVersionStatus::Brouillon, 'note' => 'Brouillon de travail non soumis.', 'creator' => 'adherent@maison-botanique.test', 'submitted' => null],
        ];

        foreach ($versions as $version) {
            FormulaVersion::query()->updateOrCreate(
                [
                    'formula_id' => Formula::query()->where('internal_code', $version['formula'])->valueOrFail('id'),
                    'version_number' => $version['number'],
                ],
                [
                    'workflow_status' => $version['status'],
                    'submitted_at' => $version['submitted'],
                    'version_note' => $version['note'],
                    'created_by' => User::query()->where('email', $version['creator'])->valueOrFail('id'),
                ],
            );
        }
    }
}
