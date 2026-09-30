<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('subscription_payments')->select('transaction_id')->whereNotNull('transaction_id')->groupBy('transaction_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Resolve duplicate subscription payment transaction IDs before adding the unique constraint.');
        }

        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->enum('status', ['pending', 'pending_verification', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending')->change();
            $table->unique('transaction_id', 'subscription_payments_transaction_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('subscription_payments')->where('status', 'pending_verification')->exists()) {
            throw new RuntimeException('Verify pending subscription payments before rolling back this migration.');
        }

        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])->default('pending')->change();
            $table->dropUnique('subscription_payments_transaction_id_unique');
        });
    }
};
