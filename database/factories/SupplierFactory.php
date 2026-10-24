<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
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
            'country_code' => Country::factory(),
            'website' => fake()->optional()->url(),
            'contact_name' => fake()->optional()->name(),
            'email' => fake()->optional()->companyEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'is_active' => true,
        ];
    }
}
