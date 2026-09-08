<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $image = 'https://example.com/project.jpg';

        return [
            'title' => fake()->sentence(4),
            'details' => fake()->paragraph(),
            'image_path' => $image,
            'image_paths' => [$image],
            'order_column' => 0,
            'service_id' => null,
        ];
    }

    public function forService(Service $service): static
    {
        return $this->state(fn (array $attributes): array => [
            'service_id' => $service->id,
        ]);
    }
}
