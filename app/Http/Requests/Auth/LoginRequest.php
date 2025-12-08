<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $roleInput = $this->normalizedRole();
        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember');

        $user = User::where('email', $this->string('email'))->first();
        $allowedRoles = $this->allowedRoles($roleInput);

        if (
            $user &&
            (empty($allowedRoles) || in_array($user->role, $allowedRoles, true)) &&
            Hash::check($this->string('password'), $user->password)
        ) {
            Auth::login($user, $remember);
            RateLimiter::clear($this->throttleKey());
            return;
        }

        $credentialsWithRole = $this->credentialsWithRole($credentials, $roleInput);

        if (Auth::attempt($credentialsWithRole, $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        RateLimiter::hit($this->throttleKey());

        $exception = ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);

        // Redirect back to role-specific login if provided, else home.
        if ($roleParam = $this->redirectRoleParam($roleInput)) {
            $exception->redirectTo(route('login.role', ['role' => $roleParam], false));
        } else {
            $exception->redirectTo(url('/'));
        }

        throw $exception;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    private function normalizedRole(): ?string
    {
        $input = strtolower((string) ($this->route('role') ?? $this->input('role', '')));

        return match ($input) {
            'teacher' => User::ROLE_GURU,
            'guru' => User::ROLE_GURU,
            'student' => User::ROLE_SISWA,
            'siswa' => User::ROLE_SISWA,
            'admin' => User::ROLE_ADMIN,
            default => null,
        };
    }

    private function credentialsWithRole(array $credentials, ?string $role): array
    {
        if ($role) {
            $credentials['role'] = $role;
        }

        return $credentials;
    }

    private function allowedRoles(?string $normalizedRole): array
    {
        return match ($normalizedRole) {
            User::ROLE_ADMIN => [User::ROLE_ADMIN],
            User::ROLE_GURU => [User::ROLE_GURU, 'teacher'],
            User::ROLE_SISWA => [User::ROLE_SISWA, 'student'],
            default => [],
        };
    }

    private function redirectRoleParam(?string $normalizedRole): ?string
    {
        return match ($normalizedRole) {
            User::ROLE_ADMIN => 'admin',
            User::ROLE_GURU => 'guru',
            User::ROLE_SISWA => 'siswa',
            default => null,
        };
    }
}
