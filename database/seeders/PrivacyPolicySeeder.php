<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PrivacyPolicySection;
use Illuminate\Database\Seeder;

final class PrivacyPolicySeeder extends Seeder
{
    public function run(): void
    {
        if (PrivacyPolicySection::query()->withInactive()->exists()) {
            return;
        }

        /** @var list<array{title: string, title_en: string, description: string, description_en: string, order_column: int, is_active: bool}> $sections */
        $sections = require database_path('data/privacy_policy_sections.php');

        foreach ($sections as $section) {
            PrivacyPolicySection::query()->create($section);
        }
    }
}
