<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            ['legal_name' => 'Aromatics Provence SAS', 'country_code' => 'FR', 'website' => 'https://aromatics-provence.test', 'contact_name' => 'Sophie Bernard', 'email' => 'qualite@aromatics-provence.test', 'phone' => '+33 4 92 10 20 30'],
            ['legal_name' => 'BioLipids Europe GmbH', 'country_code' => 'DE', 'website' => 'https://biolipids-europe.test', 'contact_name' => 'Anna Weber', 'email' => 'certificates@biolipids-europe.test', 'phone' => '+49 30 100 200'],
            ['legal_name' => 'Iberian Natural Ingredients SL', 'country_code' => 'ES', 'website' => 'https://iberian-natural-ingredients.test', 'contact_name' => 'Carlos Vega', 'email' => 'documentacion@iberian-natural-ingredients.test', 'phone' => '+34 960 100 200'],
            ['legal_name' => 'Atlas Botanicals SARL', 'country_code' => 'MA', 'website' => 'https://atlas-botanicals.test', 'contact_name' => 'Nadia El Mansouri', 'email' => 'export@atlas-botanicals.test', 'phone' => '+212 522 100 200'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['legal_name' => $supplier['legal_name'], 'country_code' => $supplier['country_code']],
                [...$supplier, 'is_active' => true],
            );
        }
    }
}
