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
    Schema::create('customer_profiles', function (Blueprint $table) {

        $table->id();

        $table->uuid('uuid')->unique();

        $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->enum('gender', [
            'male',
            'female',
            'other'
        ])->nullable();

        $table->date('date_of_birth')->nullable();

        $table->string('nic_passport')->nullable();

        $table->string('emergency_contact_name')->nullable();

        $table->string('emergency_contact_phone')->nullable();

        $table->string('preferred_language')->default('English');

        $table->boolean('profile_completed')
            ->default(false);

        $table->timestamps();

        $table->softDeletes();

        $table->index('user_id');
        $table->index('uuid');
        $table->index('nic_passport');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
