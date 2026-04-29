<?php

namespace Database\Seeders;

use App\Models\Guru;
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
        $admin = User::factory()->create([
            'username' => 'admin',
            'role' => User::ROLE_ADMIN,
            'password' => 'password',
        ]);

        $guruDetail = Guru::create([
            'name' => 'Guru Utama',
            'identifier' => 'GURU001',
            'teaches_class' => 'X-A, X-B',
            'subject' => 'Matematika',
            'teaching_hours' => '07:00-09:00',
        ]);

        $guru = User::factory()->create([
            'username' => 'guru',
            'role' => User::ROLE_GURU,
            'id_ref' => $guruDetail->id,
            'password' => 'password',
        ]);

        $guruDetail->user()->save($guru);

        $students = collect();
        foreach (range(1, 8) as $index) {
            $studentDetail = Siswa::create([
                'name' => 'Siswa '.$index,
                'identifier' => 'NIS'.str_pad((string) $index, 4, '0', STR_PAD_LEFT),
                'classroom' => 'X-A',
            ]);

            $students->push(User::factory()->create([
                'username' => 'siswa'.$index,
                'role' => User::ROLE_SISWA,
                'id_ref' => $studentDetail->id,
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
