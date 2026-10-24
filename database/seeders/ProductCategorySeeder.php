<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Soins du visage' => ['Laboratoires Méditerranée SAS', 'Dermalab Iberia SL'],
            'Soins du corps' => ['Laboratoires Méditerranée SAS', 'Maison Botanique Provence SARL'],
            'Soins capillaires' => ['Maison Botanique Provence SARL'],
            'Hygiène' => ['Laboratoires Méditerranée SAS', 'Dermalab Iberia SL'],
            'Protection solaire' => ['Laboratoires Méditerranée SAS'],
        ];

        foreach ($categories as $name => $companyNames) {
            $category = ProductCategory::query()->updateOrCreate(
                ['name' => $name],
                ['description' => "Référentiel de certification : {$name}.", 'is_active' => true],
            );

            $category->companies()->sync(
                Company::query()->whereIn('legal_name', $companyNames)->pluck('id'),
            );
        }
    }
}
