<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Feature Type
            |--------------------------------------------------------------------------
            | boolean = enabled / disabled
            | limit   = numeric limit
            */
            $table->enum('type', [
                'boolean',
                'limit',
            ])->default('boolean');

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index([
                'is_active',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_features');
    }
};
