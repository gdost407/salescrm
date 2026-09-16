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
        foreach (['roles', 'permissions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropUnique(['slug']);
                $table->unique(['company_id', 'slug']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['roles', 'permissions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unique('slug');
                $table->dropUnique(['company_id', 'slug']);
            });
        }
    }
};
