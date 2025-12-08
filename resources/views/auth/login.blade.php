@php
    $role = request('role') ?? old('role');
    $normalizedRole = match ($role) {
        'teacher' => 'guru',
        'student' => 'siswa',
        default => $role,
    };
    $roleLabel = match ($role) {
        'admin' => 'Login Sebagai Admin',
        'teacher', 'guru' => 'Login Sebagai Guru',
        'student', 'siswa' => 'Login Sebagai Siswa',
        default => __('Log in'),
    };
@endphp
@php
    $roleClass = match ($role) {
        'admin' => 'bg-slate-900 hover:bg-slate-800 text-white',
        'teacher', 'guru' => 'bg-blue-600 hover:bg-blue-700 text-white',
        'student', 'siswa' => 'bg-emerald-500 hover:bg-emerald-600 text-white',
        default => '',
    };
@endphp

<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        @if ($normalizedRole)
            <input type="hidden" name="role" value="{{ $normalizedRole }}">
        @endif

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            <button type="submit"
                class="ms-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition {{ $roleClass ?: 'bg-indigo-600 hover:bg-indigo-700 text-white' }}">
                {{ $roleLabel }}
            </button>
        </div>
    </form>
</x-guest-layout>
