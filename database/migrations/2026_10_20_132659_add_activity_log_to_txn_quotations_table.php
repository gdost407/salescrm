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
        Schema::table('txn_quotations', function (Blueprint $table) {
            $table->json('activity_log')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('txn_quotations', function (Blueprint $table) {
            $table->dropColumn('activity_log');
        });
    }
};
