<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | User who created/performed activity
            |--------------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */
            $table->enum('activity_type', [
                'call',
                'email',
                'whatsapp',
                'visit',
                'gmeet',
                'notes',
                'followup',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Follow-up
            |--------------------------------------------------------------------------
            | Used only when activity_type = followup
            */
            $table->enum('followup_type', [
                'call',
                'visit',
                'gmeet',
            ])->nullable();

            /*
            |--------------------------------------------------------------------------
            | Activity Details
            |--------------------------------------------------------------------------
            */
            $table->string('subject')->nullable();

            $table->text('summary')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Schedule
            |--------------------------------------------------------------------------
            */
            $table->timestamp('scheduled_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'completed',
                'cancelled',
                'missed',
            ])->default('completed');

            /*
            |--------------------------------------------------------------------------
            | Additional Activity Data
            |--------------------------------------------------------------------------
            */
            $table->json('metadata')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index([
                'company_id',
                'lead_id',
            ]);

            $table->index([
                'company_id',
                'user_id',
            ]);

            $table->index([
                'company_id',
                'activity_type',
            ]);

            $table->index([
                'company_id',
                'scheduled_at',
            ]);

            $table->index([
                'company_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
