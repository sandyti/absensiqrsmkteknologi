<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(): View
    {
        $students = User::where('role', User::ROLE_SISWA)
            ->orderBy('username')
            ->get();

        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.students.index', compact('students', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'identifier' => ['nullable', 'string', 'max:50'],
            'classroom' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $className = $data['classroom'] ?? null;
        if (! empty($data['class_id'])) {
            $className = SchoolClass::find($data['class_id'])?->name;
        }

        DB::transaction(function () use ($data, $className): void {
            $student = Siswa::create([
                'name' => $data['name'],
                'identifier' => $data['identifier'] ?? null,
                'classroom' => $className,
            ]);

            User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password'] ?? 'password'),
                'role' => User::ROLE_SISWA,
                'id_ref' => $student->id,
            ]);
        });

        return back()->with('status', 'Siswa berhasil ditambahkan.');
    }

    public function edit(User $student): View
    {
        abort_unless($student->isSiswa(), 404);

        $classes = SchoolClass::orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        abort_unless($student->isSiswa(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$student->id],
            'identifier' => ['nullable', 'string', 'max:50'],
            'classroom' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data['classroom'] = $data['classroom'] ?? null;
        if (! empty($data['class_id'])) {
            $data['classroom'] = SchoolClass::find($data['class_id'])?->name;
        }

        DB::transaction(function () use ($student, $data, $className): void {
            $detail = $student->siswaProfile ?: new Siswa();
            $detail->fill([
                'name' => $data['name'],
                'identifier' => $data['identifier'] ?? null,
                'classroom' => $className,
            ])->save();

            $student->forceFill([
                'username' => $data['username'],
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $student->password,
            ])->save();

            $student->forceFill(['id_ref' => $detail->id])->save();
        });

        return redirect()->route('students.index')->with('status', 'Data siswa diperbarui.');
    }

    public function destroy(User $student): RedirectResponse
    {
        abort_unless($student->isSiswa(), 404);

        $student->delete();

        return back()->with('status', 'Siswa dihapus.');
    }
}
