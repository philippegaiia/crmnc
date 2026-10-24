<?php

namespace Database\Factories;

use App\Enums\ProductCommercialStatus;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
            'product_category_id' => ProductCategory::factory(),
            'name' => fake()->words(3, true),
            'brand' => fake()->company(),
            'white_label_client' => fake()->optional()->company(),
            'commercial_status' => ProductCommercialStatus::Brouillon,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
