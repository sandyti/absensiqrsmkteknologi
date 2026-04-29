<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::with(['teacher', 'students'])->orderBy('name')->get();
        $teachers = User::where('role', User::ROLE_GURU)->orderBy('username')->get();
        $students = User::where('role', User::ROLE_SISWA)->orderBy('username')->get();
        $classes = Kelas::orderBy('nama')->get();

        return view('admin.subjects.index', compact('subjects', 'teachers', 'students', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255'],
            'time_slot' => ['nullable', 'string', 'max:100'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'classroom' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'exists:school_classes,id_kelas'],
            'students' => ['array'],
            'students.*' => ['exists:users,id'],
        ]);

        $classroom = $data['classroom'] ?? null;
        if (! empty($data['class_id'])) {
            $classroom = Kelas::find($data['class_id'])?->nama;
        }

        $subject = Subject::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'time_slot' => $data['time_slot'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? null,
            'class_id' => $data['class_id'] ?? null,
            'classroom' => $classroom,
        ]);

        $subject->students()->sync($data['students'] ?? []);

        return back()->with('status', 'Mapel berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        $teachers = User::where('role', User::ROLE_GURU)->orderBy('username')->get();
        $students = User::where('role', User::ROLE_SISWA)->orderBy('username')->get();
        $classes = Kelas::orderBy('nama')->get();

        $subject->load('students');

        return view('admin.subjects.edit', compact('subject', 'teachers', 'students', 'classes'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code,'.$subject->id],
            'name' => ['required', 'string', 'max:255'],
            'time_slot' => ['nullable', 'string', 'max:100'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'classroom' => ['nullable', 'string', 'max:100'],
            'class_id' => ['nullable', 'exists:school_classes,id_kelas'],
            'students' => ['array'],
            'students.*' => ['exists:users,id'],
        ]);

        $classroom = $data['classroom'] ?? null;
        if (! empty($data['class_id'])) {
            $classroom = Kelas::find($data['class_id'])?->nama;
        }

        $subject->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'time_slot' => $data['time_slot'] ?? null,
            'teacher_id' => $data['teacher_id'] ?? null,
            'class_id' => $data['class_id'] ?? null,
            'classroom' => $classroom,
        ]);

        $subject->students()->sync($data['students'] ?? []);

        return redirect()->route('subjects.index')->with('status', 'Mapel diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return back()->with('status', 'Mapel dihapus.');
    }
}
