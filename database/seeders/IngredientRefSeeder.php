<?php

namespace Database\Seeders;

use App\Enums\IngredientOrigin;
use App\Enums\IngredientRefStatus;
use App\Enums\LabelPosition;
use App\Enums\OrganicEligibility;
use App\Models\IngredientCategory;
use App\Models\IngredientRef;
use Illuminate\Database\Seeder;

class IngredientRefSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            ['category' => 'Phase aqueuse', 'display_name' => 'Eau purifiée', 'inci' => 'AQUA', 'synonyms' => ['Water', 'Eau'], 'origin' => IngredientOrigin::Minerale, 'organic' => OrganicEligibility::Non],
            ['category' => 'Phase aqueuse', 'display_name' => 'Glycérine végétale', 'inci' => 'GLYCERIN', 'synonyms' => ['Glycerol'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Oui],
            ['category' => 'Huiles et beurres végétaux', 'display_name' => 'Huile de tournesol', 'inci' => 'HELIANTHUS ANNUUS SEED OIL', 'synonyms' => ['Sunflower Seed Oil'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Oui],
            ['category' => 'Huiles et beurres végétaux', 'display_name' => 'Beurre de karité', 'inci' => 'BUTYROSPERMUM PARKII BUTTER', 'synonyms' => ['Shea Butter'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Oui],
            ['category' => 'Actifs cosmétiques', 'display_name' => 'Aloe vera en poudre', 'inci' => 'ALOE BARBADENSIS LEAF JUICE POWDER', 'synonyms' => ['Aloe Vera'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Oui],
            ['category' => 'Actifs cosmétiques', 'display_name' => 'Niacinamide', 'inci' => 'NIACINAMIDE', 'synonyms' => ['Vitamin B3'], 'origin' => IngredientOrigin::Synthese, 'organic' => OrganicEligibility::Non],
            ['category' => 'Émulsifiants et texturants', 'display_name' => 'Gomme xanthane', 'inci' => 'XANTHAN GUM', 'synonyms' => ['E415'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Oui],
            ['category' => 'Émulsifiants et texturants', 'display_name' => 'Alcool cétéarylique', 'inci' => 'CETEARYL ALCOHOL', 'synonyms' => ['Cetostearyl Alcohol'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Conditionnel],
            ['category' => 'Émulsifiants et texturants', 'display_name' => 'Glyceryl Stearate Citrate', 'inci' => 'GLYCERYL STEARATE CITRATE', 'synonyms' => [], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Conditionnel],
            ['category' => 'Tensioactifs', 'display_name' => 'Coco-glucoside', 'inci' => 'COCO-GLUCOSIDE', 'synonyms' => ['Coco Glucoside'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Conditionnel],
            ['category' => 'Tensioactifs', 'display_name' => 'Sodium Cocoyl Glutamate', 'inci' => 'SODIUM COCOYL GLUTAMATE', 'synonyms' => [], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Conditionnel],
            ['category' => 'Conservateurs', 'display_name' => 'Benzoate de sodium', 'inci' => 'SODIUM BENZOATE', 'synonyms' => ['E211'], 'origin' => IngredientOrigin::Synthese, 'organic' => OrganicEligibility::Conditionnel],
            ['category' => 'Conservateurs', 'display_name' => 'Sorbate de potassium', 'inci' => 'POTASSIUM SORBATE', 'synonyms' => ['E202'], 'origin' => IngredientOrigin::Synthese, 'organic' => OrganicEligibility::Conditionnel],
            ['category' => 'Correcteurs et auxiliaires', 'display_name' => 'Acide citrique', 'inci' => 'CITRIC ACID', 'synonyms' => ['E330'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Conditionnel],
            ['category' => 'Parfums et substances parfumantes', 'display_name' => 'Parfum naturel lavande', 'inci' => 'PARFUM', 'synonyms' => ['Fragrance'], 'origin' => IngredientOrigin::Vegetale, 'organic' => OrganicEligibility::Conditionnel],
        ];

        foreach ($ingredients as $ingredient) {
            IngredientRef::query()->updateOrCreate(
                ['normalized_inci' => $ingredient['inci']],
                [
                    'ingredient_category_id' => IngredientCategory::query()->where('name', $ingredient['category'])->valueOrFail('id'),
                    'display_name' => $ingredient['display_name'],
                    'synonyms' => $ingredient['synonyms'],
                    'origin' => $ingredient['origin'],
                    'label_position' => LabelPosition::Autorise,
                    'conditions' => $ingredient['organic'] === OrganicEligibility::Conditionnel ? 'Vérifier le procédé, le fournisseur et la documentation justificative.' : null,
                    'organic_eligibility' => $ingredient['organic'],
                    'status' => IngredientRefStatus::Valide,
                ],
            );
        }
    }
}
