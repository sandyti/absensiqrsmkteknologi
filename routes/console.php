<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:reset-guru-passwords', function () {
    $newPassword = 'password';
    $hashed = Hash::make($newPassword);

    $count = User::where('role', User::ROLE_GURU)->update(['password' => $hashed]);

    $this->info("Updated {$count} guru passwords to '{$newPassword}'.");
})->purpose('Force reset all guru passwords to a fixed value');
