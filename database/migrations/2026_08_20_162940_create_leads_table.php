<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('alternate_mobile', 30)->nullable();
            $table->string('job_title')->nullable();
            $table->decimal('deal_amount', 15, 2)
                ->nullable()
                ->default(0);
            $table->string('company_name')->nullable();

            $table->string('status')->default('New');
            $table->string('stage')->default('New');
            $table->string('source')->default('Self');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('client_id')
                ->nullable();

            $table->timestamp('converted_at')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pincode', 20)->nullable();

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->enum('priority', [
                'low',
                'medium',
                'high',
                'urgent',
            ])->default('medium');

            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'company_id',
                'status',
            ]);

            $table->index([
                'company_id',
                'stage',
            ]);

            $table->index([
                'company_id',
                'source',
            ]);

            $table->index([
                'company_id',
                'assigned_to',
            ]);

            $table->index([
                'company_id',
                'created_by',
            ]);

            $table->index([
                'company_id',
                'client_id',
            ]);

            $table->index([
                'company_id',
                'created_at',
            ]);

            $table->index([
                'company_id',
                'deal_amount',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
