<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Service;
use App\Scopes\ActiveScope;
use Illuminate\Database\Seeder;

final class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $serviceIds = Service::query()
            ->withoutGlobalScope(ActiveScope::class)
            ->pluck('id')
            ->all();

        if ($serviceIds === []) {
            $this->command?->warn('LeadSeeder skipped: no services found. Run CatalogSeeder first.');

            return;
        }

        $this->seedBatch(8, 'pending', $serviceIds);
        $this->seedBatch(6, 'contacted', $serviceIds);
        $this->seedBatch(6, 'completed', $serviceIds);
        $this->seedBatch(5, 'closed', $serviceIds);
    }

    /**
     * @param  list<int>  $serviceIds
     */
    private function seedBatch(int $count, string $state, array $serviceIds): void
    {
        Lead::factory()
            ->count($count)
            ->{$state}()
            ->create()
            ->each(function (Lead $lead) use ($serviceIds): void {
                if (fake()->boolean(15)) {
                    return;
                }

                $selected = fake()->randomElements(
                    $serviceIds,
                    fake()->numberBetween(1, min(2, count($serviceIds))),
                );

                $lead->update(['service_id' => $selected[0]]);
                $lead->services()->sync($selected);
            });
    }
}
