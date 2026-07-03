<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_applications', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('application_status', [
                'pending',
                'approved',
                'rejected',
                'more_info_required'
            ])->default('pending');

            $table->string('first_name');
            $table->string('last_name');

            $table->string('nic_passport', 50);

            $table->date('date_of_birth');

            $table->string('phone', 20);

            $table->text('address');

            $table->string('driving_license_number');

            $table->date('license_expiry_date');

            $table->integer('years_of_experience')->default(0);

            $table->json('languages')->nullable();

            $table->json('skills')->nullable();

            $table->enum('availability', [
                'full_time',
                'part_time',
                'weekends'
            ]);

            $table->string('license_document');

            $table->string('nic_document');

            $table->string('selfie_photo');

            $table->text('admin_notes')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->index('user_id');
            $table->index('uuid');
            $table->index('application_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_applications');
    }
};