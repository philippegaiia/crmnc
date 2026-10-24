<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::query()->upsert([
            ['code' => 'FR', 'name_fr' => 'France', 'name_en' => 'France'],
            ['code' => 'ES', 'name_fr' => 'Espagne', 'name_en' => 'Spain'],
            ['code' => 'IT', 'name_fr' => 'Italie', 'name_en' => 'Italy'],
            ['code' => 'BE', 'name_fr' => 'Belgique', 'name_en' => 'Belgium'],
            ['code' => 'CH', 'name_fr' => 'Suisse', 'name_en' => 'Switzerland'],
            ['code' => 'DE', 'name_fr' => 'Allemagne', 'name_en' => 'Germany'],
            ['code' => 'GB', 'name_fr' => 'Royaume-Uni', 'name_en' => 'United Kingdom'],
            ['code' => 'PT', 'name_fr' => 'Portugal', 'name_en' => 'Portugal'],
            ['code' => 'MA', 'name_fr' => 'Maroc', 'name_en' => 'Morocco'],
            ['code' => 'TN', 'name_fr' => 'Tunisie', 'name_en' => 'Tunisia'],
        ], uniqueBy: ['code'], update: ['name_fr', 'name_en']);
    }
}
