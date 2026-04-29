<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
        ]);

        $admin = User::factory()->create([
            'username' => 'admin',
            'role' => User::ROLE_ADMIN,
            'password' => 'password',
        ]);

        $guruDetail = Guru::create([
            'nama' => 'Guru Utama',
            'nip' => 'GURU001',
        ]);

        $guru = User::factory()->create([
            'username' => 'guru',
            'role' => User::ROLE_GURU,
            'id_ref' => $guruDetail->getKey(),
            'password' => 'password',
        ]);

        $guruDetail->user()->save($guru);

        Mapel::create([
            'nama_mapel' => 'Matematika',
        ]);

        $students = collect();
        foreach (range(1, 8) as $index) {
            $studentDetail = Siswa::create([
                'nama' => 'Siswa '.$index,
                'nis' => 'NIS'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'id_kelas' => $kelas->getKey(),
            ]);

            $students->push(User::factory()->create([
                'username' => 'siswa'.$index,
                'role' => User::ROLE_SISWA,
                'id_ref' => $studentDetail->getKey(),
            ]));
        }

        // Tambahkan kehadiran contoh supaya dashboard tidak kosong.
        $students->each(function (User $student) use ($guru): void {
            $student->attendances()->create([
                'date' => now()->toDateString(),
                'status' => 'hadir',
                'recorded_by' => $guru->id,
            ]);
        });
    }
}
