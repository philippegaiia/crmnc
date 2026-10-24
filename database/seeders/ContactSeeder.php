<?php

namespace Database\Seeders;

use App\Enums\ContactRole;
use App\Enums\Language;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [
            ['company' => 'Laboratoires Méditerranée SAS', 'first_name' => 'Claire', 'last_name' => 'Martin', 'role' => ContactRole::Qualite, 'email' => 'claire.martin@laboratoires-mediterranee.test', 'phone' => '+33 6 10 20 30 40', 'preferred_language' => Language::French],
            ['company' => 'Maison Botanique Provence SARL', 'first_name' => 'Julien', 'last_name' => 'Roux', 'role' => ContactRole::Dirigeant, 'email' => 'julien.roux@maison-botanique.test', 'phone' => '+33 6 20 30 40 50', 'preferred_language' => Language::French],
            ['company' => 'Dermalab Iberia SL', 'first_name' => 'Lucía', 'last_name' => 'Navarro', 'role' => ContactRole::Formulation, 'email' => 'lucia.navarro@dermalab-iberia.test', 'phone' => '+34 610 200 300', 'preferred_language' => Language::Spanish],
        ];

        foreach ($contacts as $contact) {
            $companyId = Company::query()->where('legal_name', $contact['company'])->valueOrFail('id');

            Contact::query()->updateOrCreate(
                ['email' => $contact['email']],
                [
                    'company_id' => $companyId,
                    'first_name' => $contact['first_name'],
                    'last_name' => $contact['last_name'],
                    'role' => $contact['role'],
                    'phone' => $contact['phone'],
                    'is_primary' => true,
                    'preferred_language' => $contact['preferred_language'],
                    'is_active' => true,
                ],
            );
        }
    }
}
