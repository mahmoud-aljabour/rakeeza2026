<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leads')->where('status', 'converted')->update(['status' => 'completed']);
        DB::table('lead_notes')->where('status', 'converted')->update(['status' => 'completed']);
    }

    public function down(): void
    {
        DB::table('leads')->where('status', 'completed')->update(['status' => 'converted']);
        DB::table('lead_notes')->where('status', 'completed')->update(['status' => 'converted']);
    }
};
