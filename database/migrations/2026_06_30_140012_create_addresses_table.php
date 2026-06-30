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
        Schema::create('addresses', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('country_id')->constrained();

            $table->foreignId('province_id')->constrained();

            $table->foreignId('district_id')->constrained();

            $table->foreignId('city_id')->constrained();

            $table->foreignId('area_id')->nullable()->constrained();

            $table->string('street_address');

            $table->string('postal_code')->nullable();

            $table->decimal('latitude',10,7);

            $table->decimal('longitude',10,7);

            $table->string('landmark')->nullable();

            $table->string('google_place_id')->nullable();

            $table->geometry('location')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
