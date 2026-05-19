<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            try {
                $table->index('student_id', 'attendances_student_id_idx');
            } catch (\Throwable) {
                // Index sudah ada.
            }

            try {
                $table->index(['student_id', 'date'], 'attendances_student_id_date_idx');
            } catch (\Throwable) {
                // Index sudah ada.
            }

            try {
                $table->dropUnique('attendances_student_id_date_unique');
            } catch (\Throwable) {
                // Unique sudah tidak ada.
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_student_id_date_idx');
            $table->dropIndex('attendances_student_id_idx');
            $table->unique(['student_id', 'date'], 'attendances_student_id_date_unique');
        });
    }
};
