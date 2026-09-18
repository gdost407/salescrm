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
        Schema::create('txn_ledger', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('ledger_type', [
                'client',
                'company'
            ]);

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->restrictOnDelete();

            $table->date('transaction_date');

            $table->enum('entry_type', [
                'debit',
                'credit'
            ]);

            $table->string('transaction_type');
            // invoice, payment, expense, refund, adjustment, etc.

            $table->string('document_type')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();

            $table->decimal('amount', 15, 2);

            $table->string('reference_no')->nullable();

            $table->text('description')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['company_id', 'ledger_type', 'client_id', 'transaction_date'],
                'txn_ledger_index'
            );

            $table->index([
                'document_type',
                'document_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('txn_ledger');
    }
};
