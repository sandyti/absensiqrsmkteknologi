<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = User::where('role', User::ROLE_GURU)
            ->orderBy('username')
            ->get();

        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.teachers.index', compact('teachers', 'classes', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'identifier' => ['nullable', 'string', 'max:50'],
            'class_ids' => ['array'],
            'class_ids.*' => ['exists:school_classes,id'],
            'teaches_class' => ['nullable', 'string', 'max:200'],
            'subject_ids' => ['array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'subject' => ['nullable', 'string', 'max:200'],
            'teaching_hours' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $classNames = $this->resolveClassNames($request);
        $subjectNames = $this->resolveSubjectNames($request, $data);
        $data['teaching_hours'] = $subjectNames['time_slot'] ?? $data['teaching_hours'];

        DB::transaction(function () use ($data, $classNames, $subjectNames): void {
            $guru = Guru::create([
                'name' => $data['name'],
                'identifier' => $data['identifier'] ?? null,
                'teaches_class' => $classNames,
                'subject' => $subjectNames['names'],
                'teaching_hours' => $subjectNames['time_slot'] ?? $data['teaching_hours'],
            ]);

            User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password'] ?? 'password'),
                'role' => User::ROLE_GURU,
                'id_ref' => $guru->id,
            ]);
        });

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
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$teacher->id],
            'identifier' => ['nullable', 'string', 'max:50'],
            'class_ids' => ['array'],
            'class_ids.*' => ['exists:school_classes,id'],
            'teaches_class' => ['nullable', 'string', 'max:200'],
            'subject_ids' => ['array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'subject' => ['nullable', 'string', 'max:200'],
            'teaching_hours' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data['teaches_class'] = $this->resolveClassNames($request);
        $subjectNames = $this->resolveSubjectNames($request, $data);
        $data['subject'] = $subjectNames['names'];
        $data['teaching_hours'] = $subjectNames['time_slot'] ?? $data['teaching_hours'];

        DB::transaction(function () use ($teacher, $data, $subjectNames): void {
            $guru = $teacher->guruProfile ?: new Guru();
            $guru->fill([
                'name' => $data['name'],
                'identifier' => $data['identifier'] ?? null,
                'teaches_class' => $data['teaches_class'],
                'subject' => $subjectNames['names'],
                'teaching_hours' => $subjectNames['time_slot'] ?? $data['teaching_hours'],
            ])->save();

            $teacher->forceFill([
                'username' => $data['username'],
                'password' => ! empty($data['password']) ? Hash::make($data['password']) : $teacher->password,
            ])->save();

            $teacher->forceFill(['id_ref' => $guru->id])->save();
        });

        return redirect()->route('teachers.index')->with('status', 'Data guru diperbarui.');
    }

    public function destroy(User $teacher): RedirectResponse
    {
        abort_unless($teacher->isGuru(), 404);

        $teacher->delete();

        return back()->with('status', 'Guru dihapus.');
    }

    private function resolveClassNames(Request $request): ?string
    {
        $selected = collect($request->input('class_ids', []))
            ->filter()
            ->map(fn ($id) => SchoolClass::find($id)?->name)
            ->filter();

        $manual = collect(explode(',', (string) $request->input('teaches_class')))
            ->map(fn ($name) => trim($name))
            ->filter();

        $names = $selected->merge($manual)->filter()->unique()->values();

        return $names->isNotEmpty() ? $names->implode(', ') : null;
    }

    private function resolveSubjectNames(Request $request, array $data): array
    {
        $selected = collect($request->input('subject_ids', []))
            ->filter()
            ->map(fn ($id) => Subject::find($id))
            ->filter();

        $selectedNames = $selected->pluck('name')->filter();
        $manual = collect(explode(',', (string) $request->input('subject')))
            ->map(fn ($name) => trim($name))
            ->filter();

        $names = $selectedNames->merge($manual)->filter()->unique()->values();

        $timeSlot = $selected->first()?->time_slot;

        return [
            'names' => $names->isNotEmpty() ? $names->implode(', ') : null,
            'time_slot' => $timeSlot,
        ];
    }
}
