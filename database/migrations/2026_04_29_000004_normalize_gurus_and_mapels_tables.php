<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subject_student')) {
            Schema::dropIfExists('subject_student');
        }

        if (Schema::hasTable('gurus')) {
            if (Schema::hasColumn('gurus', 'id')) {
                DB::statement('ALTER TABLE `gurus` CHANGE `id` `id_guru` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }

            if (Schema::hasColumn('gurus', 'name')) {
                DB::statement('ALTER TABLE `gurus` CHANGE `name` `nama` VARCHAR(255) NOT NULL');
            }

            if (Schema::hasColumn('gurus', 'identifier')) {
                DB::statement('ALTER TABLE `gurus` CHANGE `identifier` `nip` VARCHAR(255) NULL');
            }

            foreach (['teaches_class', 'subject', 'teaching_hours'] as $column) {
                if (Schema::hasColumn('gurus', $column)) {
                    Schema::table('gurus', function (Blueprint $table) use ($column): void {
                        $table->dropColumn($column);
                    });
                }
            }

            if (Schema::hasColumn('gurus', 'nip')) {
                try {
                    DB::statement('ALTER TABLE `gurus` ADD UNIQUE KEY `gurus_nip_unique` (`nip`)');
                } catch (\Throwable) {
                    // Ignore if the index already exists in a local environment.
                }
            }
        }

        if (Schema::hasTable('subjects')) {
            if (Schema::hasColumn('subjects', 'id')) {
                DB::statement('ALTER TABLE `subjects` CHANGE `id` `id_mapel` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }

            if (Schema::hasColumn('subjects', 'name')) {
                DB::statement('ALTER TABLE `subjects` CHANGE `name` `nama_mapel` VARCHAR(255) NOT NULL');
            }

            foreach (['teacher_id', 'class_id'] as $column) {
                if (Schema::hasColumn('subjects', $column)) {
                    Schema::table('subjects', function (Blueprint $table) use ($column): void {
                        try {
                            $table->dropForeign([$column]);
                        } catch (\Throwable) {
                            // Ignore if the constraint name differs across environments.
                        }
                    });
                }
            }

            foreach (['code', 'time_slot', 'teacher_id', 'classroom', 'class_id'] as $column) {
                if (Schema::hasColumn('subjects', $column)) {
                    Schema::table('subjects', function (Blueprint $table) use ($column): void {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        if (Schema::hasTable('attendance_sessions')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                try {
                    $table->dropForeign(['subject_id']);
                } catch (\Throwable) {
                    // Ignore if the foreign key name differs.
                }
            });

            Schema::table('attendance_sessions', function (Blueprint $table): void {
                $table->foreign('subject_id')->references('id_mapel')->on('subjects')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_sessions')) {
            Schema::table('attendance_sessions', function (Blueprint $table): void {
                try {
                    $table->dropForeign(['subject_id']);
                } catch (\Throwable) {
                    //
                }
                $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            });
        }

        if (Schema::hasTable('subjects')) {
            if (Schema::hasColumn('subjects', 'id_mapel')) {
                DB::statement('ALTER TABLE `subjects` CHANGE `id_mapel` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }

            if (Schema::hasColumn('subjects', 'nama_mapel')) {
                DB::statement('ALTER TABLE `subjects` CHANGE `nama_mapel` `name` VARCHAR(255) NOT NULL');
            }
        }

        if (Schema::hasTable('gurus')) {
            if (Schema::hasColumn('gurus', 'id_guru')) {
                DB::statement('ALTER TABLE `gurus` CHANGE `id_guru` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }

            if (Schema::hasColumn('gurus', 'nama')) {
                DB::statement('ALTER TABLE `gurus` CHANGE `nama` `name` VARCHAR(255) NOT NULL');
            }

            if (Schema::hasColumn('gurus', 'nip')) {
                DB::statement('ALTER TABLE `gurus` CHANGE `nip` `identifier` VARCHAR(255) NULL');
            }
        }
    }
};
