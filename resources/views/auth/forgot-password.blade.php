<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? No problem. Just let us know your username and we will send you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Username -->
        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" value="Role" />
            <select id="role" name="role" class="block mt-1 w-full border-gray-300 rounded" required>
                <option value="">Pilih Role</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                <option value="guru" @selected(old('role') === 'guru')>Guru</option>
                <option value="siswa" @selected(old('role') === 'siswa')>Siswa</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Send Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
