<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('id_kelas')->nullable()->after('nama_mapel')->constrained('kelas', 'id_kelas')->nullOnDelete();
            $table->string('jam_pelajaran', 100)->nullable()->after('id_kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_kelas');
            $table->dropColumn('jam_pelajaran');
        });
    }
};
