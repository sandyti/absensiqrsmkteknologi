<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
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
            $guru = $user->guruProfile ?: new Guru();
            $guru->fill([
                'nama' => $validated['name'],
                'nip' => $user->guruProfile?->nip,
            ])->save();

            $user->forceFill(['id_ref' => $guru->getKey()])->save();
        }

        if ($user->isSiswa()) {
            $siswa = $user->siswaProfile ?: new Siswa();
            $siswa->fill([
                'nama' => $validated['name'],
                'nis' => $user->siswaProfile?->nis,
                'id_kelas' => $user->siswaProfile?->id_kelas,
            ])->save();

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
            $user->guruProfile()?->delete();
            $user->siswaProfile()?->delete();
            $user->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
