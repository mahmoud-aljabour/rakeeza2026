<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PrivacyPolicySection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrivacyPolicySection>
 */
class PrivacyPolicySectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'title_en' => fake()->unique()->sentence(3),
            'description' => fake()->paragraph(),
            'description_en' => fake()->paragraph(),
            'order_column' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
