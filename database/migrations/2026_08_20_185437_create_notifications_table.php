<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Notification
            |--------------------------------------------------------------------------
            */
            $table->string('type');

            $table->string('title');

            $table->text('message');

            /*
            |--------------------------------------------------------------------------
            | Related Records
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('lead_id')->nullable();

            $table->unsignedBigInteger('client_id')->nullable();

            $table->unsignedBigInteger('activity_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Notification Status
            |--------------------------------------------------------------------------
            */
            $table->boolean('is_read')->default(false);

            $table->timestamp('read_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Extra Data
            |--------------------------------------------------------------------------
            */
            $table->json('data')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index([
                'company_id',
                'user_id',
                'is_read',
            ]);

            $table->index([
                'company_id',
                'lead_id',
            ]);

            $table->index([
                'company_id',
                'client_id',
            ]);

            $table->index([
                'company_id',
                'activity_id',
            ]);

            $table->index([
                'user_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
