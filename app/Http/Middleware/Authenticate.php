<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use App\Models\User;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        $role = $this->detectRole($request);

        if ($role) {
            return route('login.role', ['role' => $role], false);
        }

        return url('/');
    }

    private function detectRole(Request $request): ?string
    {
        $roleInput = $request->route('role')
            ?? $request->query('role')
            ?? $request->session()->get('login_role');

        return match (strtolower((string) $roleInput)) {
            'admin' => User::ROLE_ADMIN,
            'guru', 'teacher' => User::ROLE_GURU,
            'siswa', 'student' => User::ROLE_SISWA,
            default => null,
        };
    }
}
