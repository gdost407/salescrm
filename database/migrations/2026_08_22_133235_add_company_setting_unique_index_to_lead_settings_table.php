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
        Schema::table('lead_settings', function (Blueprint $table) {
            $table->unique(['company_id', 'setting_type', 'name'], 'lead_settings_company_type_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_settings', function (Blueprint $table) {
            $table->dropUnique('lead_settings_company_type_name_unique');
        });
    }
};
