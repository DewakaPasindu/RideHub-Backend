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
        Schema::create('vehicle_images', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identification
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Vehicle
            |--------------------------------------------------------------------------
            */

            $table->foreignId('vehicle_id')
                ->constrained('vehicles')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Image Type / Position
            |--------------------------------------------------------------------------
            |
            | Each vehicle can have one image for each required position:
            | front, rear, left_side, right_side, interior.
            |
            */

            $table->enum('image_type', [
                'front',
                'rear',
                'left_side',
                'right_side',
                'interior',
            ]);

            /*
            |--------------------------------------------------------------------------
            | File Information
            |--------------------------------------------------------------------------
            */

            $table->string('original_name');

            $table->string('stored_name');

            $table->string('disk')->default('public');

            $table->string('file_path');

            $table->string('mime_type');

            $table->string('extension');

            $table->unsignedBigInteger('file_size');

            /*
            |--------------------------------------------------------------------------
            | Display
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_primary')->default(false);

            $table->unsignedInteger('sort_order')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes / Constraints
            |--------------------------------------------------------------------------
            */

            $table->index('uuid');

            $table->index('vehicle_id');

            $table->index('image_type');

            $table->index('is_primary');

            $table->unique([
                'vehicle_id',
                'image_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_images');
    }
};