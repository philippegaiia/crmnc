<?php

namespace Database\Seeders;

use App\Enums\OrganicCertificationStatus;
use App\Models\Company;
use App\Models\IngredientRef;
use App\Models\LabIngredient;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class LabIngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            ['company' => 'Laboratoires Méditerranée SAS', 'inci' => 'AQUA', 'supplier' => 'Aromatics Provence SAS', 'name' => 'AquaPur Cosmetics', 'reference' => 'AP-AQUA-100', 'grade' => 'Cosmétique', 'organic_status' => OrganicCertificationStatus::Conventional, 'organic_percentage' => 0],
            ['company' => 'Laboratoires Méditerranée SAS', 'inci' => 'GLYCERIN', 'supplier' => 'BioLipids Europe GmbH', 'name' => 'Glycerin Bio 99.5', 'reference' => 'BL-GLY-BIO', 'grade' => 'Biologique', 'organic_status' => OrganicCertificationStatus::Certified, 'organic_percentage' => 99.5],
            ['company' => 'Laboratoires Méditerranée SAS', 'inci' => 'HELIANTHUS ANNUUS SEED OIL', 'supplier' => 'BioLipids Europe GmbH', 'name' => 'Sunflower Organic Refined', 'reference' => 'BL-SUN-ORG', 'grade' => 'Biologique', 'organic_status' => OrganicCertificationStatus::Certified, 'organic_percentage' => 100],
            ['company' => 'Laboratoires Méditerranée SAS', 'inci' => 'NIACINAMIDE', 'supplier' => 'Iberian Natural Ingredients SL', 'name' => 'NiaPure PC', 'reference' => 'INI-NIA-01', 'grade' => 'Cosmétique', 'organic_status' => OrganicCertificationStatus::Conventional, 'organic_percentage' => 0],
            ['company' => 'Maison Botanique Provence SARL', 'inci' => 'HELIANTHUS ANNUUS SEED OIL', 'supplier' => 'Atlas Botanicals SARL', 'name' => 'Huile de tournesol biologique', 'reference' => 'AB-HVT-24', 'grade' => 'Biologique', 'organic_status' => OrganicCertificationStatus::Certified, 'organic_percentage' => 100],
            ['company' => 'Maison Botanique Provence SARL', 'inci' => 'PARFUM', 'supplier' => 'Aromatics Provence SAS', 'name' => 'Lavande Naturelle 184', 'reference' => 'AP-LAV-184', 'grade' => 'Parfumerie naturelle', 'organic_status' => OrganicCertificationStatus::Partial, 'organic_percentage' => 85],
            ['company' => 'Maison Botanique Provence SARL', 'inci' => 'COCO-GLUCOSIDE', 'supplier' => 'Iberian Natural Ingredients SL', 'name' => 'CocoGlucoside CG-50', 'reference' => 'INI-CG50', 'grade' => 'Cosmétique', 'organic_status' => OrganicCertificationStatus::Partial, 'organic_percentage' => 50],
        ];

        foreach ($ingredients as $ingredient) {
            LabIngredient::query()->updateOrCreate(
                [
                    'company_id' => Company::query()->where('legal_name', $ingredient['company'])->valueOrFail('id'),
                    'supplier_id' => Supplier::query()->where('legal_name', $ingredient['supplier'])->valueOrFail('id'),
                    'supplier_reference' => $ingredient['reference'],
                ],
                [
                    'ingredient_ref_id' => IngredientRef::query()->where('normalized_inci', $ingredient['inci'])->valueOrFail('id'),
                    'commercial_name' => $ingredient['name'],
                    'grade' => $ingredient['grade'],
                    'organic_certification_status' => $ingredient['organic_status'],
                    'organic_content_percentage' => $ingredient['organic_percentage'],
                    'notes' => 'Ingrédient de démonstration, sélectionnable avant ou pendant sa validation.',
                ],
            );
        }
    }
}
