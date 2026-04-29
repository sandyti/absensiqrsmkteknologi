<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->renameIfExists('users', 'user');
        $this->renameIfExists('siswas', 'siswa');
        $this->renameIfExists('gurus', 'guru');
        $this->renameIfExists('presensis', 'presensi');
        $this->renameIfExists('sesi_presensis', 'sesi_presensi');
        $this->renameIfExists('jadwals', 'jadwal');
        $this->renameIfExists('school_classes', 'kelas');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->renameIfExists('kelas', 'school_classes');
        $this->renameIfExists('jadwal', 'jadwals');
        $this->renameIfExists('sesi_presensi', 'sesi_presensis');
        $this->renameIfExists('presensi', 'presensis');
        $this->renameIfExists('guru', 'gurus');
        $this->renameIfExists('siswa', 'siswas');
        $this->renameIfExists('user', 'users');

        Schema::enableForeignKeyConstraints();
    }

    private function renameIfExists(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }
};

