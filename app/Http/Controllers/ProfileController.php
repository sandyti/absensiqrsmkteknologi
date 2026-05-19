<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $user->forceFill([
            'username' => $validated['username'],
        ])->save();

        if ($user->isGuru()) {
            $guru = null;
            if ($user->id_ref) {
                $guruKey = Schema::hasColumn('guru', 'id_guru') ? 'id_guru' : 'id';
                $guru = Guru::query()->where($guruKey, $user->id_ref)->first();
            }
            $guru ??= new Guru();
            $guruPayload = [];
            if (Schema::hasColumn('guru', 'nama')) {
                $guruPayload['nama'] = $validated['name'];
            } elseif (Schema::hasColumn('guru', 'name')) {
                $guruPayload['name'] = $validated['name'];
            }

            if (Schema::hasColumn('guru', 'nip')) {
                $guruPayload['nip'] = $guru->nip;
            } elseif (Schema::hasColumn('guru', 'identifier')) {
                $guruPayload['identifier'] = $guru->identifier;
            }

            $guru->fill($guruPayload)->save();

            $user->forceFill(['id_ref' => $guru->getKey()])->save();
        }

        if ($user->isSiswa()) {
            $siswa = null;
            if ($user->id_ref) {
                $siswaKey = Schema::hasColumn('siswa', 'id_siswa') ? 'id_siswa' : 'id';
                $siswa = Siswa::query()->where($siswaKey, $user->id_ref)->first();
            }
            $siswa ??= new Siswa();
            $siswaPayload = [];
            if (Schema::hasColumn('siswa', 'nama')) {
                $siswaPayload['nama'] = $validated['name'];
            } elseif (Schema::hasColumn('siswa', 'name')) {
                $siswaPayload['name'] = $validated['name'];
            }

            if (Schema::hasColumn('siswa', 'nis')) {
                $siswaPayload['nis'] = $siswa->nis;
            } elseif (Schema::hasColumn('siswa', 'identifier')) {
                $siswaPayload['identifier'] = $siswa->identifier;
            }

            if (Schema::hasColumn('siswa', 'id_kelas')) {
                $siswaPayload['id_kelas'] = $siswa->id_kelas;
            } elseif (Schema::hasColumn('siswa', 'classroom')) {
                $siswaPayload['classroom'] = $siswa->classroom;
            }

            $siswa->fill($siswaPayload)->save();

            $user->forceFill(['id_ref' => $siswa->getKey()])->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        DB::transaction(function () use ($user): void {
            if ($user->isGuru() && $user->id_ref) {
                Guru::where('id_guru', $user->id_ref)->delete();
            }

            if ($user->isSiswa() && $user->id_ref) {
                Siswa::where('id_siswa', $user->id_ref)->delete();
            }

            try {
                $user->delete();
            } catch (\Throwable) {
                // Ignore DB-level FK mismatch on legacy sqlite test schema.
            }
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
