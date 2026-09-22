<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('txn_quotations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            $table->string('quotation_no');

            $table->date('quotation_date');
            $table->date('valid_until')->nullable();

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->enum('status', [
                'draft',
                'sent',
                'accepted',
                'rejected',
                'expired',
                'cancelled'
            ])->default('draft');

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['company_id', 'quotation_no']);
            $table->index(['company_id', 'client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('txn_quotations');
    }
};
