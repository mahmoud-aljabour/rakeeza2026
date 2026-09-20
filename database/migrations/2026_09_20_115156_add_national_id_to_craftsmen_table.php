<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('craftsmen', function (Blueprint $table): void {
            $table->string('national_id', 9)->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('craftsmen', function (Blueprint $table): void {
            $table->dropUnique(['national_id']);
            $table->dropColumn('national_id');
        });
    }
};
