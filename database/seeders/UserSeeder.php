<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Phil', 'email' => 'phil@mail.com', 'role' => UserRole::Admin, 'company' => null],
            ['name' => 'Gestionnaire Démo', 'email' => 'gestionnaire@cosmebio.test', 'role' => UserRole::Gestionnaire, 'company' => null],
            ['name' => 'Évaluatrice Démo', 'email' => 'evaluateur@cosmebio.test', 'role' => UserRole::Evaluateur, 'company' => null],
            ['name' => 'Claire Martin', 'email' => 'adherent@mediterranee.test', 'role' => UserRole::Adherent, 'company' => 'Laboratoires Méditerranée SAS'],
            ['name' => 'Julien Roux', 'email' => 'adherent@maison-botanique.test', 'role' => UserRole::Adherent, 'company' => 'Maison Botanique Provence SARL'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'password',
                    'role' => $user['role'],
                    'company_id' => $user['company'] === null
                        ? null
                        : Company::query()->where('legal_name', $user['company'])->valueOrFail('id'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
