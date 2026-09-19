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
        Schema::create('lead_service', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lead_id', 'service_id']);
        });

        $leads = DB::table('leads')->whereNotNull('service_id')->get(['id', 'service_id']);

        foreach ($leads as $lead) {
            DB::table('lead_service')->insert([
                'lead_id' => $lead->id,
                'service_id' => $lead->service_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_service');
    }
};
