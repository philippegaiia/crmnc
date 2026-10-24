<?php

namespace Database\Seeders;

use App\Enums\CompanyCommercialStatus;
use App\Enums\CompanyOrigin;
use App\Enums\Language;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'legal_name' => 'Laboratoires Méditerranée SAS',
                'usual_name' => 'Laboratoires Méditerranée',
                'commercial_status' => CompanyCommercialStatus::Converti,
                'origin' => CompanyOrigin::Salon,
                'preferred_language' => Language::French,
                'address_line_1' => '18 avenue des Calanques',
                'postal_code' => '13008',
                'city' => 'Marseille',
                'region' => 'Provence-Alpes-Côte d’Azur',
                'country_code' => 'FR',
                'phone' => '+33 4 91 00 10 20',
                'email' => 'contact@laboratoires-mediterranee.test',
                'website' => 'https://laboratoires-mediterranee.test',
                'notes_internal' => 'Adhérent actif spécialisé dans les soins visage et corps.',
            ],
            [
                'legal_name' => 'Maison Botanique Provence SARL',
                'usual_name' => 'Maison Botanique',
                'commercial_status' => CompanyCommercialStatus::Converti,
                'origin' => CompanyOrigin::Recommandation,
                'preferred_language' => Language::French,
                'address_line_1' => '7 chemin des Lavandes',
                'postal_code' => '84400',
                'city' => 'Apt',
                'region' => 'Provence-Alpes-Côte d’Azur',
                'country_code' => 'FR',
                'phone' => '+33 4 90 00 30 40',
                'email' => 'bonjour@maison-botanique.test',
                'website' => 'https://maison-botanique.test',
                'notes_internal' => 'Marque artisanale orientée huiles végétales et produits capillaires.',
            ],
            [
                'legal_name' => 'Dermalab Iberia SL',
                'usual_name' => 'Dermalab Iberia',
                'commercial_status' => CompanyCommercialStatus::DossierEnvoye,
                'origin' => CompanyOrigin::SiteWeb,
                'preferred_language' => Language::Spanish,
                'address_line_1' => 'Carrer de la Ciència 24',
                'postal_code' => '08042',
                'city' => 'Barcelona',
                'region' => 'Catalunya',
                'country_code' => 'ES',
                'phone' => '+34 930 100 200',
                'email' => 'calidad@dermalab-iberia.test',
                'website' => 'https://dermalab-iberia.test',
                'notes_internal' => 'Prospect en cours de qualification pour une gamme d’hygiène.',
            ],
        ];

        foreach ($companies as $company) {
            Company::query()->withTrashed()->updateOrCreate(
                ['legal_name' => $company['legal_name']],
                [...$company, 'deleted_at' => null],
            );
        }
    }
}
