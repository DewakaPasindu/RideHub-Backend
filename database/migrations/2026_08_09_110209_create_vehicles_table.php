<?php

use App\Core\Enums\ApplicationStatus;
use App\Core\Enums\FuelType;
use App\Core\Enums\TransmissionType;
use App\Core\Enums\VehicleType;
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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Owner
            |--------------------------------------------------------------------------
            */
            $table->foreignId('vehicle_owner_profile_id')
                ->constrained('vehicle_owner_profiles')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Vehicle Identification
            |--------------------------------------------------------------------------
            */
            $table->string('registration_number', 30)->unique();

            $table->string('make', 100);

            $table->string('model', 100);

            $table->string('variant', 100)->nullable();

            $table->unsignedSmallInteger('manufacturing_year');

            $table->string('color', 50);

            /*
            |--------------------------------------------------------------------------
            | Vehicle Specifications
            |--------------------------------------------------------------------------
            */
            $table->enum(
                'vehicle_type',
                VehicleType::values()
            );

            $table->enum(
                'fuel_type',
                FuelType::values()
            );

            $table->enum(
                'transmission',
                TransmissionType::values()
            );

            $table->unsignedTinyInteger('seating_capacity');

            $table->unsignedTinyInteger('doors')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Vehicle Identification Numbers
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('mileage')->default(0);

            $table->string('chassis_number', 100)->unique();

            $table->string('engine_number', 100)->unique();

            $table->string('vin', 100)->nullable()->unique();

            /*
            |--------------------------------------------------------------------------
            | Features
            |--------------------------------------------------------------------------
            */
            $table->boolean('has_ac')->default(false);

            $table->boolean('has_gps')->default(false);

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Approval Workflow
            |--------------------------------------------------------------------------
            */
            $table->enum(
                'application_status',
                ApplicationStatus::values()
            )->default(ApplicationStatus::DRAFT->value);

            $table->timestamp('verified_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index('uuid');

            $table->index('vehicle_owner_profile_id');

            $table->index('application_status');

            $table->index('vehicle_type');

            $table->index('fuel_type');

            $table->index('transmission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};