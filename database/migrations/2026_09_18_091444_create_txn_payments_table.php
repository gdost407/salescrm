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
        Schema::create('txn_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            $table->string('payment_no');

            $table->enum('document_type', [
                'quotation',
                'job',
                'invoice'
            ]);

            $table->unsignedBigInteger('document_id');

            $table->date('payment_date');

            $table->decimal('amount', 15, 2);

            $table->enum('payment_mode', [
                'cash',
                'bank',
                'upi',
                'card',
                'cheque',
                'other'
            ]);

            $table->string('reference_no')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['company_id', 'payment_no']);

            $table->index(
                ['company_id', 'client_id', 'document_type', 'document_id'],
                'txn_payments_doc_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('txn_payments');
    }
};
