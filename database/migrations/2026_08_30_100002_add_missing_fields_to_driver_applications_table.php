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
        Schema::table('driver_applications', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->default(5.00)->after('availability');
            $table->integer('review_count')->default(0)->after('rating');
            $table->string('availability_status', 20)->default('available')->after('review_count');
            $table->decimal('location_lat', 10, 7)->nullable()->after('availability_status');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_applications', function (Blueprint $table) {
            $table->dropColumn([
                'rating',
                'review_count',
                'availability_status',
                'location_lat',
                'location_lng'
            ]);
        });
    }
};
