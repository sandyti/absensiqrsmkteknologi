<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

        Schema::table('attendances', function (Blueprint $table) {
            // Ensure supporting indexes exist.
            try {
                $table->index('student_id', 'attendances_student_id_idx');
            } catch (\Throwable $e) {
                // Index already exists.
            }

            try {
                $table->index(['student_id', 'date'], 'attendances_student_id_date_idx');
            } catch (\Throwable $e) {
                // Index already exists.
            }

            // Drop unique that blocks multiple attendance per day.
            try {
                $table->dropUnique('attendances_student_id_date_unique');
            } catch (\Throwable $e) {
                // Unique already removed.
            }
        });

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
                $table->dropIndex('attendances_student_id_date_idx');
            } catch (\Throwable $e) {
                // Index might not exist.
            }

            try {
                $table->dropIndex('attendances_student_id_idx');
            } catch (\Throwable $e) {
                // Index might not exist.
            }

            try {
                $table->unique(['student_id', 'date'], 'attendances_student_id_date_unique');
            } catch (\Throwable $e) {
                // Unique might already exist.
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            } catch (\Throwable $e) {
                // FK already exists or failed to add; ignore.
            }
        });
    }
};
