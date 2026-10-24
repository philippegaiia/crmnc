<?php

namespace Database\Seeders;

use App\Models\FormulaLine;
use App\Models\FormulaVersion;
use App\Models\IngredientRef;
use App\Models\LabIngredient;
use Illuminate\Database\Seeder;

class FormulaLineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $compositions = [
            'HYDRA-001:1' => [
                ['AQUA', 73.2], ['GLYCERIN', 5], ['HELIANTHUS ANNUUS SEED OIL', 12], ['GLYCERYL STEARATE CITRATE', 5], ['CETEARYL ALCOHOL', 4], ['SODIUM BENZOATE', 0.8],
            ],
            'HYDRA-001:2' => [
                ['AQUA', 70.2], ['GLYCERIN', 5], ['HELIANTHUS ANNUUS SEED OIL', 12], ['GLYCERYL STEARATE CITRATE', 5], ['CETEARYL ALCOHOL', 4], ['NIACINAMIDE', 3], ['SODIUM BENZOATE', 0.8],
            ],
            'DOUCHE-002:1' => [
                ['AQUA', 69.5], ['COCO-GLUCOSIDE', 15], ['SODIUM COCOYL GLUTAMATE', 10], ['GLYCERIN', 5], ['POTASSIUM SORBATE', 0.5],
            ],
            'HUILE-001:1' => [
                ['HELIANTHUS ANNUUS SEED OIL', 88], ['BUTYROSPERMUM PARKII BUTTER', 10], ['PARFUM', 2],
            ],
            'SHAMP-002:1' => [
                ['AQUA', 75], ['COCO-GLUCOSIDE', 15], ['SODIUM COCOYL GLUTAMATE', 10],
            ],
        ];

        foreach ($compositions as $versionReference => $lines) {
            [$formulaCode, $versionNumber] = explode(':', $versionReference);
            $version = FormulaVersion::query()
                ->where('version_number', $versionNumber)
                ->whereHas('formula', fn ($query) => $query->where('internal_code', $formulaCode))
                ->firstOrFail();

            foreach ($lines as $index => [$inci, $percentage]) {
                $ingredientRefId = IngredientRef::query()->where('normalized_inci', $inci)->valueOrFail('id');
                $labIngredientId = LabIngredient::query()
                    ->where('ingredient_ref_id', $ingredientRefId)
                    ->whereHas('company.formulas.versions', fn ($query) => $query->whereKey($version->id))
                    ->value('id');

                FormulaLine::query()->updateOrCreate(
                    ['formula_version_id' => $version->id, 'position' => $index + 1],
                    [
                        'ingredient_ref_id' => $ingredientRefId,
                        'lab_ingredient_id' => $labIngredientId,
                        'percentage' => $percentage,
                        'is_qsp' => false,
                        'technical_comment' => null,
                    ],
                );
            }
        }
    }
}
