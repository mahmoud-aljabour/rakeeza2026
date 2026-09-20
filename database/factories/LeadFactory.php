<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '059'.fake()->unique()->numerify('#######'),
            'email' => fake()->boolean(70) ? fake()->unique()->safeEmail() : null,
            'service_id' => null,
            'message' => fake()->boolean(80) ? fake()->sentence(12) : null,
            'status' => LeadStatus::Pending,
            'completed_price' => null,
            'completed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LeadStatus::Pending,
            'completed_price' => null,
            'completed_at' => null,
        ]);
    }

    public function contacted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LeadStatus::Contacted,
            'completed_price' => null,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LeadStatus::Completed,
            'completed_price' => fake()->randomFloat(2, 80, 2800),
            'completed_at' => fake()->dateTimeBetween('-90 days', 'now'),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => LeadStatus::Closed,
            'completed_price' => null,
            'completed_at' => null,
        ]);
    }
}
