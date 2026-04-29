<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = User::with('guruProfile')
            ->where('role', User::ROLE_GURU)
            ->orderBy('username')
            ->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:gurus,nip'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($data): void {
            $guru = Guru::create([
                'nama' => $data['nama'],
                'nip' => $data['nip'] ?? null,
            ]);

            User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password'] ?? 'password'),
                'role' => User::ROLE_GURU,
                'id_ref' => $guru->getKey(),
            ]);
        });

        return back()->with('status', 'Guru berhasil ditambahkan.');
    }

    public function edit(User $teacher): View
    {
        abort_unless($teacher->isGuru(), 404);

        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher): RedirectResponse
    {
        abort_unless($teacher->isGuru(), 404);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('gurus', 'nip')->ignore($teacher->id_ref, 'id_guru'),
            ],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$teacher->id],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($teacher, $data): void {
            $guru = $teacher->guruProfile ?: new Guru();
            $guru->fill([
                'nama' => $data['nama'],
                'nip' => $data['nip'] ?? null,
            ])->save();

            $teacher->forceFill([
                'username' => $data['username'],
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $teacher->password,
                'id_ref' => $guru->getKey(),
            ])->save();
        });

        return redirect()->route('teachers.index')->with('status', 'Data guru diperbarui.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        abort_unless($teacher->isGuru(), 404);

        DB::transaction(function () use ($teacher): void {
            $teacher->guruProfile()?->delete();
            $teacher->delete();
        });

        return back()->with('status', 'Guru dihapus.');
    }
}
