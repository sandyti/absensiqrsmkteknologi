<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kelas_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kelas')->constrained('kelas', 'id_kelas')->cascadeOnDelete();
            $table->foreignId('id_mapel')->constrained('subjects', 'id_mapel')->cascadeOnDelete();
            $table->unique(['id_kelas', 'id_mapel']);
        });

        if (Schema::hasColumn('subjects', 'id_kelas')) {
            DB::table('subjects')
                ->whereNotNull('id_kelas')
                ->orderBy('id_mapel')
                ->select(['id_kelas', 'id_mapel'])
                ->chunk(200, function ($rows): void {
                    $insert = [];
                    foreach ($rows as $row) {
                        $insert[] = [
                            'id_kelas' => $row->id_kelas,
                            'id_mapel' => $row->id_mapel,
                        ];
                    }
                    DB::table('kelas_subject')->insertOrIgnore($insert);
                });

            Schema::table('subjects', function (Blueprint $table) {
                $table->dropConstrainedForeignId('id_kelas');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('subjects', 'id_kelas')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->foreignId('id_kelas')->nullable()->after('nama_mapel')->constrained('kelas', 'id_kelas')->nullOnDelete();
            });

            DB::table('kelas_subject')
                ->orderBy('id')
                ->select(['id_kelas', 'id_mapel'])
                ->chunk(200, function ($rows): void {
                    foreach ($rows as $row) {
                        DB::table('subjects')
                            ->where('id_mapel', $row->id_mapel)
                            ->whereNull('id_kelas')
                            ->update(['id_kelas' => $row->id_kelas]);
                    }
                });
        }

        Schema::dropIfExists('kelas_subject');
    }
};

