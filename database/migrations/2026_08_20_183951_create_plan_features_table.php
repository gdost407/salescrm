<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                ->constrained('subscription_plans')
                ->cascadeOnDelete();

            $table->foreignId('feature_id')
                ->constrained('subscription_features')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Feature Value
            |--------------------------------------------------------------------------
            | Examples:
            | 1        = enabled
            | 0        = disabled
            | 100      = limit
            | unlimited = unlimited
            */
            $table->string('value')->default('1');

            $table->timestamps();

            $table->unique([
                'plan_id',
                'feature_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
