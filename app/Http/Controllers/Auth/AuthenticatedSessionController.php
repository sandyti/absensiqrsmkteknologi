<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request, ?string $role = null): View
    {
        $normalizedRole = $this->normalizeRole($role ?? $request->query('role'));

        return view('auth.login', [
            'requestedRole' => $normalizedRole,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $redirects = [
            'admin' => route('dashboard', absolute: false),
            'guru' => route('attendance.session', absolute: false),
            'siswa' => route('attendance.me', absolute: false),
        ];

        return redirect()->intended($redirects[$user->role] ?? route('dashboard', absolute: false));
    }

    private function normalizeRole(?string $role): ?string
    {
        if (! $role) {
            return null;
        }

        $map = [
            'teacher' => User::ROLE_GURU,
            'guru' => User::ROLE_GURU,
            'student' => User::ROLE_SISWA,
            'siswa' => User::ROLE_SISWA,
            'admin' => User::ROLE_ADMIN,
        ];

        return $map[strtolower($role)] ?? null;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
