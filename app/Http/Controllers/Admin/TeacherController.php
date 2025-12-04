<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = User::where('role', User::ROLE_GURU)
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.teachers.index', compact('teachers', 'classes', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'identifier' => ['nullable', 'string', 'max:50'],
            'teaches_class' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
            'subject' => ['nullable', 'string', 'max:100'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'teaching_hours' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $className = $data['teaches_class'] ?? null;
        if (! empty($data['class_id'])) {
            $className = SchoolClass::find($data['class_id'])?->name;
        }

        $subjectName = $data['subject'] ?? null;
        if (! empty($data['subject_id'])) {
            $subjectName = Subject::find($data['subject_id'])?->name;
            $data['teaching_hours'] = Subject::find($data['subject_id'])?->time_slot;
        }

        User::create([
            ...$data,
            'teaches_class' => $className,
            'subject' => $subjectName,
            'password' => Hash::make($data['password'] ?? 'password'),
            'role' => User::ROLE_GURU,
        ]);

        return back()->with('status', 'Guru berhasil ditambahkan.');
    }

    public function edit(User $teacher): View
    {
        abort_unless($teacher->isGuru(), 404);

        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.teachers.edit', compact('teacher', 'classes', 'subjects'));
    }

    public function update(Request $request, User $teacher): RedirectResponse
    {
        abort_unless($teacher->isGuru(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$teacher->id],
            'identifier' => ['nullable', 'string', 'max:50'],
            'teaches_class' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
            'subject' => ['nullable', 'string', 'max:100'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'teaching_hours' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data['teaches_class'] = $data['teaches_class'] ?? null;
        if (! empty($data['class_id'])) {
            $data['teaches_class'] = SchoolClass::find($data['class_id'])?->name;
        }

        $data['subject'] = $data['subject'] ?? null;
        if (! empty($data['subject_id'])) {
            $data['subject'] = Subject::find($data['subject_id'])?->name;
            $data['teaching_hours'] = Subject::find($data['subject_id'])?->time_slot;
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $teacher->update($data);

        return redirect()->route('teachers.index')->with('status', 'Data guru diperbarui.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        abort_unless($teacher->isGuru(), 404);

        $teacher->delete();

        return back()->with('status', 'Guru dihapus.');
    }
}
