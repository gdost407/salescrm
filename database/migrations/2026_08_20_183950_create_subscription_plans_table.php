<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | User Limit
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('max_users')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);

            $table->string('currency', 10)->default('USD');

            /*
            |--------------------------------------------------------------------------
            | Payment Gateway Plan IDs
            |--------------------------------------------------------------------------
            */
            $table->string('monthly_gateway_plan_id')->nullable();
            $table->string('yearly_gateway_plan_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
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
        Schema::dropIfExists('subscription_plans');
    }
};
