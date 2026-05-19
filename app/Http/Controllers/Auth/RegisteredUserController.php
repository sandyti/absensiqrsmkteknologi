<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'classes' => Kelas::orderBy('nama')->get(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'nis' => ['required', 'string', 'max:50', 'unique:siswa,nis'],
            'id_kelas' => ['nullable', 'exists:kelas,id_kelas'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated, $request): User {
            $studentPayload = [];
            if (Schema::hasColumn('siswa', 'nama')) {
                $studentPayload['nama'] = $validated['nama'];
            } elseif (Schema::hasColumn('siswa', 'name')) {
                $studentPayload['name'] = $validated['nama'];
            }

            if (Schema::hasColumn('siswa', 'nis')) {
                $studentPayload['nis'] = $validated['nis'];
            } elseif (Schema::hasColumn('siswa', 'identifier')) {
                $studentPayload['identifier'] = $validated['nis'];
            }

            if (array_key_exists('id_kelas', $validated)) {
                if (Schema::hasColumn('siswa', 'id_kelas')) {
                    $studentPayload['id_kelas'] = $validated['id_kelas'];
                } elseif (Schema::hasColumn('siswa', 'classroom')) {
                    $studentPayload['classroom'] = null;
                }
            }

            $student = Siswa::query()->create($studentPayload);

            return User::create([
                'username' => $validated['username'],
                'role' => User::ROLE_SISWA,
                'password' => Hash::make($validated['password']),
                'id_ref' => $student->getKey(),
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
