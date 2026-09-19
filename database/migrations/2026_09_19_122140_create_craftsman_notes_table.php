<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('craftsman_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('craftsman_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['craftsman_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('craftsman_notes');
    }
};
