<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['subjects', 'attendance_sessions'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table): void {
                    try {
                        $table->dropForeign(['class_id']);
                    } catch (\Throwable) {
                        // Ignore environments where the constraint name differs.
                    }
                });
            }
        }

        if (Schema::hasTable('school_classes')) {
            if (Schema::hasColumn('school_classes', 'id')) {
                DB::statement('ALTER TABLE `school_classes` CHANGE `id` `id_kelas` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }

            if (Schema::hasColumn('school_classes', 'name')) {
                DB::statement('ALTER TABLE `school_classes` CHANGE `name` `nama` VARCHAR(255) NOT NULL');
            }

            if (! Schema::hasColumn('school_classes', 'tingkat')) {
                Schema::table('school_classes', function (Blueprint $table): void {
                    $table->string('tingkat')->nullable()->after('nama');
                });
            }
        }

        if (Schema::hasTable('siswas')) {
            if (Schema::hasColumn('siswas', 'id')) {
                DB::statement('ALTER TABLE `siswas` CHANGE `id` `id_siswa` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }

            if (Schema::hasColumn('siswas', 'name')) {
                DB::statement('ALTER TABLE `siswas` CHANGE `name` `nama` VARCHAR(255) NOT NULL');
            }

            if (Schema::hasColumn('siswas', 'identifier')) {
                DB::statement('ALTER TABLE `siswas` CHANGE `identifier` `nis` VARCHAR(255) NULL');
            }

            if (! Schema::hasColumn('siswas', 'id_kelas')) {
                Schema::table('siswas', function (Blueprint $table): void {
                    $table->unsignedBigInteger('id_kelas')->nullable()->after('nis');
                });
            }

            if (Schema::hasTable('school_classes')) {
                $kelasMap = DB::table('school_classes')->pluck('id_kelas', 'nama');
                DB::table('siswas')->orderBy('id_siswa')->chunkById(100, function ($rows) use ($kelasMap): void {
                    foreach ($rows as $row) {
                        $kelasId = $kelasMap[$row->classroom] ?? null;

                        DB::table('siswas')->where('id_siswa', $row->id_siswa)->update([
                            'id_kelas' => $kelasId,
                        ]);
                    }
                }, 'id_siswa');
            }

            if (Schema::hasColumn('siswas', 'classroom')) {
                Schema::table('siswas', function (Blueprint $table): void {
                    $table->dropColumn('classroom');
                });
            }

            if (! Schema::hasColumn('siswas', 'nis')) {
                Schema::table('siswas', function (Blueprint $table): void {
                    $table->string('nis')->nullable()->after('nama');
                });
            }

            if (Schema::hasColumn('siswas', 'nis')) {
                DB::statement('ALTER TABLE `siswas` ADD UNIQUE KEY `siswas_nis_unique` (`nis`)');
            }

            if (Schema::hasTable('school_classes')) {
                Schema::table('siswas', function (Blueprint $table): void {
                    $table->foreign('id_kelas')->references('id_kelas')->on('school_classes')->nullOnDelete();
                });
            }
        }

        foreach (['subjects', 'attendance_sessions'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->foreign('class_id')->references('id_kelas')->on('school_classes')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['subjects', 'attendance_sessions'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table): void {
                    try {
                        $table->dropForeign(['class_id']);
                    } catch (\Throwable) {
                        //
                    }
                    $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('siswas')) {
            Schema::table('siswas', function (Blueprint $table): void {
                try {
                    $table->dropForeign(['id_kelas']);
                } catch (\Throwable) {
                    //
                }
                try {
                    $table->dropUnique('siswas_nis_unique');
                } catch (\Throwable) {
                    //
                }
            });

            if (Schema::hasColumn('siswas', 'id_siswa')) {
                DB::statement('ALTER TABLE `siswas` CHANGE `id_siswa` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }

            if (Schema::hasColumn('siswas', 'nama')) {
                DB::statement('ALTER TABLE `siswas` CHANGE `nama` `name` VARCHAR(255) NOT NULL');
            }

            if (Schema::hasColumn('siswas', 'nis')) {
                DB::statement('ALTER TABLE `siswas` CHANGE `nis` `identifier` VARCHAR(255) NULL');
            }

            if (Schema::hasColumn('siswas', 'id_kelas')) {
                Schema::table('siswas', function (Blueprint $table): void {
                    $table->string('classroom')->nullable()->after('identifier');
                });
                if (Schema::hasTable('school_classes')) {
                    DB::table('siswas')->orderBy('id')->chunkById(100, function ($rows): void {
                        foreach ($rows as $row) {
                            $kelasName = DB::table('school_classes')->where('id_kelas', $row->id_kelas)->value('nama');
                            DB::table('siswas')->where('id', $row->id)->update([
                                'classroom' => $kelasName,
                            ]);
                        }
                    }, 'id');
                }
                Schema::table('siswas', function (Blueprint $table): void {
                    $table->dropColumn('id_kelas');
                });
            }
        }

        if (Schema::hasTable('school_classes')) {
            if (Schema::hasColumn('school_classes', 'id_kelas')) {
                DB::statement('ALTER TABLE `school_classes` CHANGE `id_kelas` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            }

            if (Schema::hasColumn('school_classes', 'nama')) {
                DB::statement('ALTER TABLE `school_classes` CHANGE `nama` `name` VARCHAR(255) NOT NULL');
            }

            if (Schema::hasColumn('school_classes', 'tingkat')) {
                Schema::table('school_classes', function (Blueprint $table): void {
                    $table->dropColumn('tingkat');
                });
            }
        }
    }
};
