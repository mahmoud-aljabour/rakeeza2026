<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->decimal('completed_price', 12, 2)->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('completed_price')->index();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex(['completed_at']);
            $table->dropColumn(['completed_price', 'completed_at']);
        });
    }
};
