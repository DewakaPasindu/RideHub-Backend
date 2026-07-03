<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            /*
             |--------------------------------------------------------------------------
             | Polymorphic Relationship
             |--------------------------------------------------------------------------
             */

            $table->morphs('documentable');

            /*
             |--------------------------------------------------------------------------
             | Document Information
             |--------------------------------------------------------------------------
             */

            $table->string('document_type');

            $table->string('original_name');

            $table->string('stored_name');

            $table->string('disk')->default('public');

            $table->string('file_path');

            $table->string('mime_type');

            $table->string('extension');

            $table->unsignedBigInteger('file_size');

            /*
             |--------------------------------------------------------------------------
             | Verification
             |--------------------------------------------------------------------------
             */

            $table->enum('status',[
                'pending',
                'verified',
                'rejected',
                'expired'
            ])->default('pending');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            $table->text('remarks')->nullable();

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
             | Indexes
             |--------------------------------------------------------------------------
             */

            $table->index('uuid');

            $table->index('status');

            $table->index('document_type');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};