<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->string('shortname', 3);
            $table->string('name', 150);
            $table->integer('phonecode');
        });

        Schema::create('states', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->string('name', 150);
            $table->unsignedInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->cascadeOnDelete();
            $table->index(['country_id', 'name']);
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->string('name', 150);
            $table->unsignedInteger('state_id');
            $table->foreign('state_id')->references('id')->on('states')->cascadeOnDelete();
            $table->index(['state_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
