<?php

namespace Database\Seeders;

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
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
            'identifier' => 'ADMIN001',
            'password' => 'password',
        ]);

        $guru = User::factory()->create([
            'name' => 'Guru Utama',
            'email' => 'guru@example.com',
            'role' => User::ROLE_GURU,
            'identifier' => 'GURU001',
            'password' => 'password',
        ]);

        $students = User::factory(8)->create([
            'role' => User::ROLE_SISWA,
        ]);

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
