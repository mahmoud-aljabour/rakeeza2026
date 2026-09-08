<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->json('image_paths')->nullable()->after('image_path');
        });

        foreach (DB::table('projects')->orderBy('id')->get() as $project) {
            $paths = is_string($project->image_path) && $project->image_path !== ''
                ? [$project->image_path]
                : [];

            DB::table('projects')->where('id', $project->id)->update([
                'image_paths' => json_encode($paths),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn('image_paths');
        });
    }
};
