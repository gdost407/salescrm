<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            $table->foreignId('integration_id')
                ->nullable()
                ->constrained('integrations')
                ->nullOnDelete();

            $table->string('event')->nullable();
            $table->string('request_id')->nullable()->index();

            $table->json('payload')->nullable();
            $table->json('response')->nullable();

            $table->unsignedSmallInteger('status_code')->nullable();

            $table->enum('status', [
                'received',
                'processing',
                'success',
                'failed',
            ])->default('received');

            $table->text('error_message')->nullable();

            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'event']);
            $table->index(['integration_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
