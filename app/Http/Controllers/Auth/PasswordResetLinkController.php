<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'string'],
        ]);

        $role = $this->normalizeRole($request->string('role'));
        if (! $role) {
            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['role' => 'Role tidak valid.']);
        }

        $user = User::where('email', $request->string('email'))->where('role', $role)->first();

        if (! $user) {
            return back()->withInput($request->only('email', 'role'))
                ->withErrors(['email' => __('auth.user')]);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            [
                'email' => $user->email,
            ]
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email', 'role'))
                ->withErrors(['email' => __($status)]);
    }

    private function normalizeRole(string $role): ?string
    {
        return match (strtolower($role)) {
            'admin' => User::ROLE_ADMIN,
            'guru', 'teacher' => User::ROLE_GURU,
            'siswa', 'student' => User::ROLE_SISWA,
            default => null,
        };
    }
}
