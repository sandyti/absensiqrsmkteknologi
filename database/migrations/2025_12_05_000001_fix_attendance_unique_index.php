<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK so unique/index changes are allowed.
        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->dropForeign(['student_id']);
            } catch (\Throwable $e) {
                // FK might already be dropped.
            }
        });

        // Ensure supporting indexes exist (skip if already there).
        if (! $this->indexExists('attendances', 'attendances_student_id_idx')) {
            DB::statement('CREATE INDEX attendances_student_id_idx ON attendances(student_id)');
        }
        if (! $this->indexExists('attendances', 'attendances_student_id_date_idx')) {
            DB::statement('CREATE INDEX attendances_student_id_date_idx ON attendances(student_id, date)');
        }

        // Drop unique that blocks multiple attendance per day.
        try {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropUnique('attendances_student_id_date_unique');
            });
        } catch (\Throwable $e) {
            // Unique already removed.
        }

        // Re-attach FK for student_id.
        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            } catch (\Throwable $e) {
                // FK already exists.
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->dropForeign(['student_id']);
            } catch (\Throwable $e) {
                // FK might already be dropped.
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->unique(['student_id', 'date'], 'attendances_student_id_date_unique');
            } catch (\Throwable $e) {
                // Unique might already exist.
            }
        });

        // Drop indexes only if they exist.
        if ($this->indexExists('attendances', 'attendances_student_id_date_idx')) {
            DB::statement('DROP INDEX attendances_student_id_date_idx ON attendances');
        }
        if ($this->indexExists('attendances', 'attendances_student_id_idx')) {
            DB::statement('DROP INDEX attendances_student_id_idx ON attendances');
        }

        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            } catch (\Throwable $e) {
                // FK already exists or failed to add; ignore.
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);

        return ! empty($result);
    }
};
