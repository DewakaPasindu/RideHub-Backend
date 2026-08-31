<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('driver_availability', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('driver_profile_id')->constrained('driver_applications')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_available')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->unique(['driver_profile_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_availability');
    }
};
