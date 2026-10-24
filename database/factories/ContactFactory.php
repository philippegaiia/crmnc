<?php

namespace Database\Factories;

use App\Enums\ContactRole;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'role' => fake()->randomElement(ContactRole::cases()),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'is_primary' => false,
            'is_active' => true,
        ];
    }
}
