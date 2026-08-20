<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('type');

            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('webhook_token')->nullable();

            $table->json('configuration')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
