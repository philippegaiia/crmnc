<?php

namespace Database\Seeders;

use App\Enums\ProductCommercialStatus;
use App\Models\Company;
use App\Models\FormulaVersion;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['company' => 'Laboratoires Méditerranée SAS', 'category' => 'Soins du visage', 'name' => 'Crème Visage Hydratante Bio', 'brand' => 'Méditerranée Essentielle', 'formula' => 'HYDRA-001', 'version' => 2, 'status' => ProductCommercialStatus::Brouillon],
            ['company' => 'Laboratoires Méditerranée SAS', 'category' => 'Hygiène', 'name' => 'Gel Douche Douceur Marine', 'brand' => 'Méditerranée Essentielle', 'formula' => 'DOUCHE-002', 'version' => 1, 'status' => ProductCommercialStatus::Brouillon],
            ['company' => 'Maison Botanique Provence SARL', 'category' => 'Soins du corps', 'name' => 'Huile Sèche Lavande', 'brand' => 'Maison Botanique', 'formula' => 'HUILE-001', 'version' => 1, 'status' => ProductCommercialStatus::Commercialise],
            ['company' => 'Maison Botanique Provence SARL', 'category' => 'Soins capillaires', 'name' => 'Shampooing Végétal Doux', 'brand' => 'Maison Botanique', 'formula' => 'SHAMP-002', 'version' => 1, 'status' => ProductCommercialStatus::Brouillon],
        ];

        foreach ($products as $product) {
            $companyId = Company::query()->where('legal_name', $product['company'])->valueOrFail('id');
            $formulaVersionId = FormulaVersion::query()
                ->where('version_number', $product['version'])
                ->whereHas('formula', fn ($query) => $query->where('company_id', $companyId)->where('internal_code', $product['formula']))
                ->valueOrFail('id');

            Product::query()->updateOrCreate(
                ['company_id' => $companyId, 'name' => $product['name']],
                [
                    'product_category_id' => ProductCategory::query()->where('name', $product['category'])->valueOrFail('id'),
                    'brand' => $product['brand'],
                    'white_label_client' => null,
                    'current_formula_version_id' => $formulaVersionId,
                    'commercial_status' => $product['status'],
                    'notes' => 'Donnée de démonstration représentative du processus de certification.',
                ],
            );
        }
    }
}
