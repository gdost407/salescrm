<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('subscription_id')
                ->constrained('company_subscriptions')
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained('subscription_plans')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */
            $table->decimal('amount', 10, 2);

            $table->string('currency', 10)->default('USD');

            $table->string('gateway');

            $table->string('transaction_id')->nullable();

            $table->string('payment_order_id')->nullable();

            $table->string('payment_method')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Payment Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'cancelled',
            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Gateway Response
            |--------------------------------------------------------------------------
            */
            $table->json('gateway_response')->nullable();

            $table->text('failure_reason')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index([
                'company_id',
                'status',
            ]);

            $table->index('transaction_id');

            $table->index('payment_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
