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
        Schema::create('presensis', function (Blueprint $table): void {
            $table->id('id_presensi');
            $table->foreignId('id_sesi')->constrained('sesi_presensis', 'id_sesi')->cascadeOnDelete();
            $table->foreignId('id_siswa')->constrained('siswas', 'id_siswa')->cascadeOnDelete();
            $table->foreignId('edited_by')->nullable()->constrained('gurus', 'id_guru')->nullOnDelete();
            $table->enum('status', ['hadir', 'izin', 'terlambat', 'sakit', 'alpa']);
            $table->timestamp('scanned_at');
            $table->enum('method', ['scan', 'manual']);

            $table->unique(['id_sesi', 'id_siswa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
