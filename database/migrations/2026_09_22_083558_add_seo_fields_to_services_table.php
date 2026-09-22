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
        Schema::table('services', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->text('body')->nullable()->after('description_en');
            $table->text('body_en')->nullable()->after('body');
            $table->string('seo_title')->nullable()->after('body_en');
            $table->string('seo_title_en')->nullable()->after('seo_title');
            $table->string('meta_description', 160)->nullable()->after('seo_title_en');
            $table->string('meta_description_en', 160)->nullable()->after('meta_description');
        });

        /** @var array<string, array<string, string>> $pages */
        $pages = require database_path('data/service_pages.php');

        foreach (DB::table('services')->orderBy('id')->get() as $row) {
            $page = $pages[$row->title] ?? null;

            if (is_array($page)) {
                DB::table('services')->where('id', $row->id)->update([
                    'slug' => $page['slug'],
                    'body' => $page['body'],
                    'body_en' => $page['body_en'],
                    'seo_title' => $page['seo_title'],
                    'seo_title_en' => $page['seo_title_en'],
                    'meta_description' => $page['meta_description'],
                    'meta_description_en' => $page['meta_description_en'],
                ]);

                continue;
            }

            DB::table('services')->where('id', $row->id)->update([
                'slug' => 'service-'.$row->id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn([
                'slug',
                'body',
                'body_en',
                'seo_title',
                'seo_title_en',
                'meta_description',
                'meta_description_en',
            ]);
        });
    }
};
