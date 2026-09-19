<?php

declare(strict_types=1);

use App\Models\Project;
use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->string('title_en')->nullable()->after('title');
            $table->text('description_en')->nullable()->after('description');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->string('title_en')->nullable()->after('title');
            $table->text('details_en')->nullable()->after('details');
        });

        $catalog = require lang_path('en/catalog.php');
        $serviceCatalog = is_array($catalog['services'] ?? null) ? $catalog['services'] : [];
        $projectCatalog = is_array($catalog['projects'] ?? null) ? $catalog['projects'] : [];

        Service::query()->withoutGlobalScopes()->each(function (Service $service) use ($serviceCatalog): void {
            $entry = $serviceCatalog[$service->title] ?? null;

            if (! is_array($entry)) {
                return;
            }

            $service->forceFill([
                'title_en' => is_string($entry['title'] ?? null) ? $entry['title'] : null,
                'description_en' => is_string($entry['description'] ?? null) ? $entry['description'] : null,
            ])->saveQuietly();
        });

        Project::query()->each(function (Project $project) use ($projectCatalog): void {
            $entry = $projectCatalog[$project->title] ?? null;

            if (! is_array($entry)) {
                return;
            }

            $project->forceFill([
                'title_en' => is_string($entry['title'] ?? null) ? $entry['title'] : null,
                'details_en' => is_string($entry['details'] ?? null) ? $entry['details'] : null,
            ])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn(['title_en', 'description_en']);
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['title_en', 'details_en']);
        });
    }
};
