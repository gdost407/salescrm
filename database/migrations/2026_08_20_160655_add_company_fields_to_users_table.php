<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (! Schema::hasColumn('users', 'company_id')) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', [
                    'owner',
                    'staff',
                ])
                    ->default('staff')
                    ->after('company_id');
            }

            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')
                    ->nullable()
                    ->after('user_type')
                    ->constrained('roles')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile', 30)
                    ->nullable()
                    ->after('email');
            }

            if (! Schema::hasColumn('users', 'joining_date')) {
                $table->date('joining_date')
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'job_role')) {
                $table->string('job_role')
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country')
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'state')) {
                $table->string('state')
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city')
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'zip_code')) {
                $table->string('zip_code', 20)
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'working_time')) {
                $table->string('working_time')
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'salary_type')) {
                $table->enum('salary_type', [
                    'monthly',
                    'weekly',
                    'daily',
                    'hourly',
                ])
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'salary')) {
                $table->decimal('salary', 15, 2)
                    ->nullable();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true);
            }

            $existingIndexes = DB::select(
                "SELECT DISTINCT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'"
            );
            $indexNames = array_map(
                static fn (object $index): string => $index->INDEX_NAME,
                $existingIndexes
            );

            if (! in_array('users_company_id_user_type_is_active_index', $indexNames, true)) {
                $table->index([
                    'company_id',
                    'user_type',
                    'is_active',
                ]);
            }

            if (! in_array('users_company_id_department_id_index', $indexNames, true)) {
                $table->index([
                    'company_id',
                    'department_id',
                ]);
            }
        });

        $foreignKeyColumns = DB::select(
            "SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND REFERENCED_TABLE_NAME IS NOT NULL"
        );
        $foreignKeyColumnNames = array_map(
            static fn (object $foreignKey): string => $foreignKey->COLUMN_NAME,
            $foreignKeyColumns
        );

        Schema::table('users', function (Blueprint $table) use ($foreignKeyColumnNames): void {
            if (! in_array('role_id', $foreignKeyColumnNames, true)) {
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropForeign(['company_id']);
            $table->dropForeign(['role_id']);

            $table->dropIndex([
                'company_id',
                'user_type',
                'is_active',
            ]);

            $table->dropIndex([
                'company_id',
                'department_id',
            ]);

            $table->dropColumn([
                'company_id',
                'user_type',
                'role_id',
                'mobile',
                'joining_date',
                'department_id',
                'job_role',
                'address',
                'country',
                'state',
                'city',
                'zip_code',
                'working_time',
                'salary_type',
                'salary',
                'is_active',
            ]);
        });
    }
};
