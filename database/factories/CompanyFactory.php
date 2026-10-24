<?php

namespace Database\Factories;

use App\Enums\CompanyCommercialStatus;
use App\Enums\Language;
use App\Models\Company;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'legal_name' => fake()->company(),
            'usual_name' => fake()->optional()->company(),
            'commercial_status' => CompanyCommercialStatus::DemandeInfo,
            'preferred_language' => Language::French,
            'address_line_1' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'country_code' => Country::factory(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
        ];
    }
}
