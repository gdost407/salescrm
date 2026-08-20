<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Company / Plan
            |--------------------------------------------------------------------------
            */
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained('subscription_plans')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Billing
            |--------------------------------------------------------------------------
            */
            $table->enum('billing_cycle', [
                'monthly',
                'yearly',
            ]);

            $table->decimal('amount', 10, 2);

            $table->string('currency', 10)->default('USD');

            /*
            |--------------------------------------------------------------------------
            | Subscription Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'active',
                'cancelled',
                'expired',
                'past_due',
                'suspended',
            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */
            $table->timestamp('starts_at')->nullable();

            $table->timestamp('ends_at')->nullable();

            $table->timestamp('cancelled_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Auto Renewal
            |--------------------------------------------------------------------------
            */
            $table->boolean('auto_renew')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Payment Gateway
            |--------------------------------------------------------------------------
            */
            $table->string('gateway')->nullable();

            $table->string('gateway_customer_id')->nullable();

            $table->string('gateway_subscription_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Extra Gateway Data
            |--------------------------------------------------------------------------
            */
            $table->json('gateway_data')->nullable();

            $table->timestamps();

            $table->index([
                'company_id',
                'status',
            ]);

            $table->index([
                'company_id',
                'ends_at',
            ]);

            $table->index('gateway_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_subscriptions');
    }
};
