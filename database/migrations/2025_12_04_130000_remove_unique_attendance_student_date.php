<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan ada index pendukung untuk FK student_id sebelum unique dilepas.
        try {
            DB::statement('ALTER TABLE attendances ADD INDEX attendances_student_id_idx (student_id)');
        } catch (\Throwable $e) {
            // Index sudah ada, lanjut.
        }

        // Tambahkan index kombinasional non-unique agar query tetap efisien.
        try {
            DB::statement('ALTER TABLE attendances ADD INDEX attendances_student_id_date_idx (student_id, date)');
        } catch (\Throwable $e) {
            // Index sudah ada, lanjut.
        }

        // Lepas constraint unique agar multiple scan per hari bisa tersimpan.
        DB::statement('ALTER TABLE attendances DROP INDEX attendances_student_id_date_unique');
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
