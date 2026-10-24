<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Formula;
use Illuminate\Database\Seeder;

class FormulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formulas = [
            ['company' => 'Laboratoires Méditerranée SAS', 'internal_code' => 'HYDRA-001', 'description' => 'Crème visage hydratante destinée à une demande de certification biologique.'],
            ['company' => 'Laboratoires Méditerranée SAS', 'internal_code' => 'DOUCHE-002', 'description' => 'Gel douche doux sans sulfates pour usage quotidien.'],
            ['company' => 'Maison Botanique Provence SARL', 'internal_code' => 'HUILE-001', 'description' => 'Huile sèche corps au tournesol et parfum naturel de lavande.'],
            ['company' => 'Maison Botanique Provence SARL', 'internal_code' => 'SHAMP-002', 'description' => 'Shampooing doux végétal en cours de formulation.'],
        ];

        foreach ($formulas as $formula) {
            $companyId = Company::query()->where('legal_name', $formula['company'])->valueOrFail('id');

            Formula::query()->updateOrCreate(
                ['company_id' => $companyId, 'internal_code' => $formula['internal_code']],
                ['description' => $formula['description'], 'is_active' => true],
            );
        }
    }
}
