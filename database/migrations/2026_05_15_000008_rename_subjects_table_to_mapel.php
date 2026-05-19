<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subjects') || Schema::hasTable('mapel')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('kelas_subject')) {
            $this->dropForeignByColumnIfExists('kelas_subject', 'id_mapel');
        }

        if (Schema::hasTable('jadwal')) {
            $this->dropForeignByColumnIfExists('jadwal', 'id_mapel');
        }

        if (Schema::hasTable('attendance_sessions') && Schema::hasColumn('attendance_sessions', 'subject_id')) {
            $this->dropForeignByColumnIfExists('attendance_sessions', 'subject_id');
        }

        Schema::rename('subjects', 'mapel');

        if (Schema::hasTable('kelas_subject')) {
            Schema::table('kelas_subject', function (Blueprint $table): void {
                $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('jadwal')) {
            Schema::table('jadwal', function (Blueprint $table): void {
                $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('attendance_sessions') && Schema::hasColumn('attendance_sessions', 'subject_id')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->foreign('subject_id')->references('id_mapel')->on('mapel')->nullOnDelete();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        if (! Schema::hasTable('mapel') || Schema::hasTable('subjects')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('kelas_subject')) {
            $this->dropForeignByColumnIfExists('kelas_subject', 'id_mapel');
        }

        if (Schema::hasTable('jadwal')) {
            $this->dropForeignByColumnIfExists('jadwal', 'id_mapel');
        }

        if (Schema::hasTable('attendance_sessions') && Schema::hasColumn('attendance_sessions', 'subject_id')) {
            $this->dropForeignByColumnIfExists('attendance_sessions', 'subject_id');
        }

        Schema::rename('mapel', 'subjects');

        if (Schema::hasTable('kelas_subject')) {
            Schema::table('kelas_subject', function (Blueprint $table): void {
                $table->foreign('id_mapel')->references('id_mapel')->on('subjects')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('jadwal')) {
            Schema::table('jadwal', function (Blueprint $table): void {
                $table->foreign('id_mapel')->references('id_mapel')->on('subjects')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('attendance_sessions') && Schema::hasColumn('attendance_sessions', 'subject_id')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->foreign('subject_id')->references('id_mapel')->on('subjects')->nullOnDelete();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    private function dropForeignByColumnIfExists(string $table, string $column): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $database = DB::getDatabaseName();

        $rows = DB::select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        foreach ($rows as $row) {
            $constraint = $row->CONSTRAINT_NAME ?? null;
            if (! is_string($constraint) || $constraint === '') {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                $table,
                $constraint
            ));
        }
    }
};
