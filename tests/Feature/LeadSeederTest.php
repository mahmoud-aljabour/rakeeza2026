<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Service;
use Database\Seeders\LeadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LeadSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_seeder_creates_leads_across_all_statuses(): void
    {
        Service::factory()->count(3)->create();

        $this->seed(LeadSeeder::class);

        $this->assertSame(25, Lead::query()->count());
        $this->assertSame(8, Lead::query()->where('status', LeadStatus::Pending)->count());
        $this->assertSame(6, Lead::query()->where('status', LeadStatus::Contacted)->count());
        $this->assertSame(6, Lead::query()->where('status', LeadStatus::Completed)->count());
        $this->assertSame(5, Lead::query()->where('status', LeadStatus::Closed)->count());
        $this->assertTrue(
            Lead::query()->where('status', LeadStatus::Completed)->whereNotNull('completed_price')->exists(),
        );
    }

    public function test_lead_seeder_skips_when_no_services_exist(): void
    {
        $this->seed(LeadSeeder::class);

        $this->assertSame(0, Lead::query()->count());
    }
}
