<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /** @var array<string, array<string, string>> $pages */
        $pages = require database_path('data/service_pages.php');

        foreach ($pages as $page) {
            DB::table('services')->where('slug', $page['slug'])->update([
                'body' => $page['body'],
                'body_en' => $page['body_en'],
            ]);
        }
    }

    public function down(): void
    {
        // The previous essays are not stored. Restore an older version from the admin if needed.
    }
};
