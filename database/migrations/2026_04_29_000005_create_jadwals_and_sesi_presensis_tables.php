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
        Schema::create('jadwals', function (Blueprint $table): void {
            $table->id('id_jadwal');
            $table->foreignId('id_kelas')->constrained('school_classes', 'id_kelas')->cascadeOnDelete();
            $table->foreignId('id_mapel')->constrained('subjects', 'id_mapel')->cascadeOnDelete();
            $table->foreignId('id_guru')->constrained('gurus', 'id_guru')->cascadeOnDelete();
            $table->string('hari', 20);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
        });

        Schema::create('sesi_presensis', function (Blueprint $table): void {
            $table->id('id_sesi');
            $table->foreignId('id_jadwal')->constrained('jadwals', 'id_jadwal')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('token')->unique();
            $table->enum('status', ['open', 'closed'])->default('open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_presensis');
        Schema::dropIfExists('jadwals');
    }
};
