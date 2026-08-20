<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_settings', function (Blueprint $table) {
            $table->id();

            // NULL = system/default setting
            // company_id = company-specific setting
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnDelete();

            // status / stage / source
            $table->enum('setting_type', [
                'status',
                'stage',
                'source',
            ]);

            $table->string('name');

            // system = seeded/fixed
            // manual = created by company owner
            $table->enum('type', [
                'system',
                'manual',
            ])->default('system');

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index([
                'company_id',
                'setting_type',
                'is_active',
            ]);

            $table->index([
                'setting_type',
                'type',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_settings');
    }
};
