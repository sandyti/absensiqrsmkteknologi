<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        $students = User::with('siswaProfile.kelas')
            ->where('role', User::ROLE_SISWA)
            ->orderBy('username')
            ->get();

        $classes = Kelas::orderBy('nama')->get();
        return view('admin.students.index', compact('students', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'nis' => ['required', 'string', 'max:50', 'unique:siswas,nis'],
            'id_kelas' => ['nullable', 'exists:school_classes,id_kelas'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($data): void {
            $student = Siswa::create([
                'nama' => $data['nama'],
                'nis' => $data['nis'],
                'id_kelas' => $data['id_kelas'] ?? null,
            ]);

            User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password'] ?? 'password'),
                'role' => User::ROLE_SISWA,
                'id_ref' => $student->getKey(),
            ]);
        });

        return back()->with('status', 'Siswa berhasil ditambahkan.');
    }

    public function edit(User $student): View
    {
        abort_unless($student->isSiswa(), 404);

        $classes = Kelas::orderBy('nama')->get();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->isSiswa(), 404);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$student->id],
            'nis' => [
                'required',
                'string',
                'max:50',
                Rule::unique('siswas', 'nis')->ignore($student->id_ref, 'id_siswa'),
            ],
            'id_kelas' => ['nullable', 'exists:school_classes,id_kelas'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($student, $data): void {
            $detail = $student->siswaProfile ?: new Siswa();
            $detail->fill([
                'nama' => $data['nama'],
                'nis' => $data['nis'],
                'id_kelas' => $data['id_kelas'] ?? null,
            ])->save();

            $student->forceFill([
                'username' => $data['username'],
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $student->password,
            ])->save();

            $student->forceFill(['id_ref' => $detail->getKey()])->save();
        });

        return redirect()->route('students.index')->with('status', 'Data siswa diperbarui.');
    }

    public function destroy(User $student): RedirectResponse
    {
        abort_unless($student->isSiswa(), 404);

        DB::transaction(function () use ($student): void {
            $student->siswaProfile()?->delete();
            $student->delete();
        });

        return back()->with('status', 'Siswa dihapus.');
    }
}
