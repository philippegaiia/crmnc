<?php

namespace Database\Seeders;

use App\Models\IngredientCategory;
use Illuminate\Database\Seeder;

class IngredientCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Phase aqueuse' => 'Eaux, hydrolats et solvants aqueux.',
            'Huiles et beurres végétaux' => 'Corps gras d’origine végétale.',
            'Actifs cosmétiques' => 'Ingrédients apportant une fonction cosmétique ciblée.',
            'Émulsifiants et texturants' => 'Émulsifiants, co-émulsifiants et agents de texture.',
            'Tensioactifs' => 'Agents lavants et moussants.',
            'Conservateurs' => 'Système de protection microbiologique.',
            'Parfums et substances parfumantes' => 'Parfums et compositions aromatiques.',
            'Correcteurs et auxiliaires' => 'Ajusteurs de pH, chélatants et auxiliaires de formulation.',
        ];

        foreach ($categories as $name => $description) {
            IngredientCategory::query()->updateOrCreate(
                ['name' => $name],
                ['description' => $description, 'is_active' => true],
            );
        }
    }
}
