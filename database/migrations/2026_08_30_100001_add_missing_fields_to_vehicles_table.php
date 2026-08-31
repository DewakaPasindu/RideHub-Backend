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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('price_per_day', 10, 2)->default(0)->after('has_gps');
            $table->string('nearest_town', 100)->nullable()->after('price_per_day');
            $table->decimal('location_lat', 10, 7)->nullable()->after('nearest_town');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            $table->json('features')->nullable()->after('location_lng');
            $table->json('images')->nullable()->after('features');
            $table->string('rejection_reason', 500)->nullable()->after('admin_notes');
            $table->date('available_from')->nullable()->after('rejection_reason');
            $table->date('available_to')->nullable()->after('available_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'price_per_day',
                'nearest_town',
                'location_lat',
                'location_lng',
                'features',
                'images',
                'rejection_reason',
                'available_from',
                'available_to'
            ]);
        });
    }
};
