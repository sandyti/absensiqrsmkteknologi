<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:reset-passwords {role} {--password=password}', function (string $role) {
    $newPassword = $this->option('password') ?: 'password';
    $hashed = Hash::make($newPassword);

    $roleMap = [
        'admin' => User::ROLE_ADMIN,
        'guru' => User::ROLE_GURU,
        'siswa' => User::ROLE_SISWA,
        'teacher' => User::ROLE_GURU,
        'student' => User::ROLE_SISWA,
    ];

    $normalizedRole = $roleMap[strtolower($role)] ?? null;

    if (! $normalizedRole) {
        $this->error('Role tidak dikenali. Gunakan admin|guru|siswa.');
        return;
    }

    $count = User::where('role', $normalizedRole)->update(['password' => $hashed]);

    $this->info("Updated {$count} {$normalizedRole} passwords to '{$newPassword}'.");
})->purpose('Force reset passwords by role (admin/guru/siswa)');
