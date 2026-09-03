<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::connection(null)->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        Schema::dropIfExists('rental_locations');
        Schema::dropIfExists('rental_handovers');
        Schema::dropIfExists('rental_condition_photos');
        Schema::dropIfExists('rental_vehicle_conditions');
        Schema::dropIfExists('rental_documents');
        Schema::dropIfExists('rental_applications');

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 1. Rental Applications table
        Schema::create('rental_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('status', 50)->default('draft'); // draft, submitted, under_review, owner_approved, ready_for_handover, active, returned, completed, owner_rejected, more_information_required, cancelled
            
            // Personal & verification info
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 30);
            $table->string('email', 150);
            $table->string('address', 500);
            $table->string('id_type', 30); // nic, passport
            $table->string('id_number', 50);
            $table->string('driving_license_number', 100);
            $table->date('license_expiry_date');
            
            // Location info
            $table->text('pickup_address');
            $table->decimal('pickup_latitude', 10, 7);
            $table->decimal('pickup_longitude', 10, 7);
            $table->text('return_address');
            $table->decimal('return_latitude', 10, 7);
            $table->decimal('return_longitude', 10, 7);
            
            // Rental timing & requirements
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedTinyInteger('passenger_count')->default(1);
            $table->string('luggage_requirement', 50)->default('medium'); // light, medium, heavy
            $table->string('rental_purpose', 255)->nullable();
            $table->text('additional_requirements')->nullable();
            $table->text('more_info_reason')->nullable();
            
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('uuid');
            $table->index('customer_id');
            $table->index('vehicle_id');
            $table->index('status');
        });

        // 2. Rental Documents table (secure/private verification files)
        Schema::create('rental_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('rental_application_id')->constrained('rental_applications')->cascadeOnDelete();
            $table->string('document_type', 50); // id_front, id_back, driving_license_front, driving_license_back, customer_live_photo
            $table->string('document_path', 500);
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size');
            $table->string('verification_status', 30)->default('pending'); // pending, verified, rejected
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();

            $table->index('uuid');
            $table->index('rental_application_id');
        });

        // 3. Rental Vehicle Conditions table
        Schema::create('rental_vehicle_conditions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('rental_application_id')->constrained('rental_applications')->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->string('inspection_stage', 30); // pre_rental, return
            $table->unsignedInteger('odometer_reading');
            $table->unsignedTinyInteger('fuel_level'); // 0-100 percentage
            $table->text('exterior_condition')->nullable();
            $table->text('interior_condition')->nullable();
            $table->json('existing_damage')->nullable();
            $table->text('condition_description')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('rental_application_id');
        });

        // 4. Rental Condition Photos table
        Schema::create('rental_condition_photos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('condition_id')->constrained('rental_vehicle_conditions')->cascadeOnDelete();
            $table->string('photo_type', 50); // front, rear, left, right, interior, odometer, fuel, damage, other
            $table->string('file_path', 500);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('captured_at')->useCurrent();
            $table->timestamps();

            $table->index('uuid');
            $table->index('condition_id');
        });

        // 5. Rental Handovers table
        Schema::create('rental_handovers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('rental_application_id')->constrained('rental_applications')->cascadeOnDelete();
            $table->string('status', 50)->default('pending'); // pending, customer_confirmed, owner_confirmed, completed
            $table->timestamp('customer_confirmed_at')->nullable();
            $table->timestamp('owner_confirmed_at')->nullable();
            $table->timestamp('handover_at')->nullable();
            $table->decimal('handover_latitude', 10, 7)->nullable();
            $table->decimal('handover_longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('rental_application_id');
        });

        // 6. Rental Locations table (GPS pings during ACTIVE status)
        Schema::create('rental_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_application_id')->constrained('rental_applications')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 6, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index('rental_application_id');
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::connection(null)->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        Schema::dropIfExists('rental_locations');
        Schema::dropIfExists('rental_handovers');
        Schema::dropIfExists('rental_condition_photos');
        Schema::dropIfExists('rental_vehicle_conditions');
        Schema::dropIfExists('rental_documents');
        Schema::dropIfExists('rental_applications');

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};
