<?php

declare(strict_types=1);

use App\Enums\CraftsmanStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('craftsmen', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('city');
            $table->string('specialty');
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->boolean('has_tools')->default(false);
            $table->text('bio')->nullable();
            $table->string('status')->default(CraftsmanStatus::Pending->value)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('craftsmen');
    }
};
