<?php

namespace App\Services;

use App\Models\FormulaLine;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductReadinessService
{
    public function __construct(
        private readonly LabIngredientStatusService $statusService,
    ) {}

    /**
     * @return array{
     *     stage: string,
     *     stage_label: string,
     *     is_ready: bool,
     *     blocking_count: int,
     *     checks: list<array{key: string, label: string, passed: bool, detail: string}>
     * }
     */
    public function assess(Product $product): array
    {
        $product->loadMissing([
            'category',
            'currentFormulaVersion.formula',
            'currentFormulaVersion.lines.labIngredient.latestReview',
            'currentFormulaVersion.lines.labIngredient.documents',
        ]);

        $version = $product->current_formula_version_id === null
            ? null
            : $product->currentFormulaVersion;
        $lines = $version === null ? collect() : $version->lines;
        $formulaTotal = (float) $lines->sum('percentage');
        $missingAssignments = $lines->whereNull('lab_ingredient_id')->count();
        $assignedLines = $lines->whereNotNull('lab_ingredient_id');
        $invalidApprovalCount = $assignedLines
            ->filter(fn (FormulaLine $line): bool => $line->labIngredient === null
                || ! $this->statusService->hasValidApproval($line->labIngredient))
            ->count();

        $checks = [
            $this->check(
                key: 'product_details',
                label: 'Informations du produit',
                passed: filled($product->name) && filled($product->brand),
                detail: 'Nom, marque et catégorie renseignés.',
            ),
            $this->check(
                key: 'formula_selected',
                label: 'Version de formule',
                passed: $version !== null,
                detail: $version === null
                    ? 'Sélectionnez une version de formule.'
                    : "Version {$version->version_number} sélectionnée.",
            ),
            $this->check(
                key: 'formula_total',
                label: 'Composition à 100 %',
                passed: $lines->isNotEmpty() && abs($formulaTotal - 100.0) <= 0.01,
                detail: $this->formulaTotalDetail($lines, $formulaTotal),
            ),
            $this->check(
                key: 'ingredient_assignments',
                label: 'Ingrédients laboratoire affectés',
                passed: $lines->isNotEmpty() && $missingAssignments === 0,
                detail: $this->assignmentDetail($lines, $missingAssignments),
            ),
            $this->check(
                key: 'ingredient_approvals',
                label: 'Ingrédients laboratoire approuvés',
                passed: $assignedLines->isNotEmpty() && $invalidApprovalCount === 0,
                detail: $this->approvalDetail($assignedLines, $invalidApprovalCount),
            ),
            $this->check(
                key: 'flat_label',
                label: "Maquette à plat de l'étiquette",
                passed: false,
                detail: "Le dépôt de l'étiquette sera ajouté dans la prochaine étape.",
            ),
        ];

        $blockingCount = collect($checks)->where('passed', false)->count();
        $isReady = $blockingCount === 0;

        return [
            'stage' => $isReady ? 'ready' : 'draft',
            'stage_label' => $isReady ? 'Prêt à soumettre' : 'Brouillon',
            'is_ready' => $isReady,
            'blocking_count' => $blockingCount,
            'checks' => $checks,
        ];
    }

    /** @return array{key: string, label: string, passed: bool, detail: string} */
    private function check(string $key, string $label, bool $passed, string $detail): array
    {
        return compact('key', 'label', 'passed', 'detail');
    }

    /** @param Collection<int, FormulaLine> $lines */
    private function formulaTotalDetail(Collection $lines, float $formulaTotal): string
    {
        if ($lines->isEmpty()) {
            return 'La formule ne contient aucune ligne.';
        }

        return 'Total actuel : '.number_format($formulaTotal, 2, ',', ' ').' %.';
    }

    /** @param Collection<int, FormulaLine> $lines */
    private function assignmentDetail(Collection $lines, int $missingAssignments): string
    {
        if ($lines->isEmpty()) {
            return "Ajoutez d'abord les lignes de formule.";
        }

        if ($missingAssignments === 0) {
            return 'Toutes les lignes ont un ingrédient laboratoire affecté.';
        }

        return $missingAssignments === 1
            ? '1 ligne de formule sans ingrédient laboratoire affecté.'
            : "{$missingAssignments} lignes de formule sans ingrédient laboratoire affecté.";
    }

    /** @param Collection<int, FormulaLine> $assignedLines */
    private function approvalDetail(Collection $assignedLines, int $invalidApprovalCount): string
    {
        if ($assignedLines->isEmpty()) {
            return "Aucun ingrédient laboratoire n'est encore affecté.";
        }

        if ($invalidApprovalCount === 0) {
            return 'Tous les ingrédients affectés disposent d’une approbation valide.';
        }

        return $invalidApprovalCount === 1
            ? "1 ingrédient affecté n'a pas d'approbation valide."
            : "{$invalidApprovalCount} ingrédients affectés n'ont pas d'approbation valide.";
    }
}
