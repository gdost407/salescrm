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
        Schema::create('txn_history_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('document_type', [
                'quotation',
                'job',
                'invoice'
            ]);

            $table->unsignedBigInteger('document_id');

            $table->foreignId('item_id')
                ->nullable()
                ->constrained('items')
                ->nullOnDelete();

            // Snapshot
            $table->enum('item_type', ['service', 'inventory']);
            $table->string('item_name');
            $table->text('description')->nullable();

            $table->string('hsn_sac')->nullable();
            $table->string('sku')->nullable();

            $table->decimal('qty', 15, 3)->default(1);
            $table->decimal('rate', 15, 2);

            $table->foreignId('tax_id')
                ->nullable()
                ->constrained('taxes')
                ->nullOnDelete();

            $table->decimal('tax_rate', 8, 4)->default(0);

            $table->enum('tax_type', [
                'exclusive',
                'inclusive'
            ])->default('exclusive');

            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index([
                'company_id',
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
        Schema::dropIfExists('txn_history_items');
    }
};
