<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeacherSessionController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();
        $classes = Kelas::orderBy('nama')->get();
        $subjects = Mapel::orderBy('nama_mapel')->get();

        $selectedClassId = $request->query('class_id');
        $selectedSubjectId = $request->query('subject_id');

        $selectedClass = $selectedClassId ? $classes->firstWhere('id_kelas', (int) $selectedClassId) : null;
        $selectedSubject = $selectedSubjectId ? $subjects->firstWhere('id_mapel', (int) $selectedSubjectId) : null;

        $students = collect();
        if ($selectedClass) {
            $students = User::where('role', User::ROLE_SISWA)
                ->whereHas('siswaProfile.kelas', fn ($q) => $q->where('nama', $selectedClass->nama))
                ->orderBy('username')
                ->get();
        }

        $activeSession = $selectedClassId && $selectedSubjectId
            ? AttendanceSession::where('teacher_id', $teacher->id)
                ->whereIn('status', ['active', 'paused'])
                ->where('class_id', $selectedClassId)
                ->where('subject_id', $selectedSubjectId)
                ->latest()
                ->first()
            : null;

        $showQr = $request->boolean('show_qr');

        $scans = collect();
        if ($activeSession && $selectedClass) {
            $scans = Attendance::with('student')
                ->whereDate('date', Carbon::today())
                ->whereHas('student.siswaProfile.kelas', function ($query) use ($selectedClass) {
                    $query->where('nama', $selectedClass->nama);
                })
                ->latest()
                ->get();
        }

        return view('attendance.session', compact(
            'classes',
            'subjects',
            'students',
            'selectedClassId',
            'selectedSubjectId',
            'selectedClass',
            'selectedSubject',
            'activeSession',
            'scans',
            'showQr'
        ));
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:school_classes,id_kelas'],
            'subject_id' => ['required', 'exists:subjects,id_mapel'],
        ]);

        $class = Kelas::find($data['class_id']);
        $subject = Mapel::find($data['subject_id']);

        $session = AttendanceSession::updateOrCreate(
            [
                'teacher_id' => $request->user()->id,
                'status' => 'active',
            ],
            [
                'class_id' => $data['class_id'],
                'subject_id' => $data['subject_id'],
                'started_at' => now(),
                'paused_at' => null,
                'ended_at' => null,
                'code' => $this->makeSessionCode($class, $subject),
            ]
        );

        $session->save();

        return redirect()->route('attendance.session', [
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
            'show_qr' => 1,
        ])->with('status', 'Sesi absensi dimulai / diperbarui.');
    }

    public function pause(Request $request): RedirectResponse
    {
        $session = AttendanceSession::where('teacher_id', $request->user()->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($session) {
            $session->update([
                'status' => 'paused',
                'paused_at' => now(),
            ]);
        }

        return redirect()->route('attendance.session', [
            'class_id' => $session?->class_id,
            'subject_id' => $session?->subject_id,
            'show_qr' => 1,
        ])->with('status', 'Sesi dijeda.');
    }

    public function resume(Request $request): RedirectResponse
    {
        $session = AttendanceSession::where('teacher_id', $request->user()->id)
            ->where('status', 'paused')
            ->latest()
            ->first();

        if ($session) {
            $session->update([
                'status' => 'active',
                'paused_at' => null,
            ]);
        }

        return redirect()->route('attendance.session', [
            'class_id' => $session?->class_id,
            'subject_id' => $session?->subject_id,
            'show_qr' => 1,
        ])->with('status', 'Sesi dilanjutkan.');
    }

    public function close(Request $request): RedirectResponse
    {
        $session = AttendanceSession::where('teacher_id', $request->user()->id)
            ->whereIn('status', ['active', 'paused'])
            ->latest()
            ->first();

        if ($session) {
            $session->update([
                'status' => 'closed',
                'ended_at' => now(),
            ]);
        }

        return redirect()->route('attendance.session', [
            'class_id' => $session?->class_id,
            'subject_id' => $session?->subject_id,
        ])->with('status', 'Sesi ditutup.');
    }

    public function markManual(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:hadir,izin,sakit,alpa,terlambat'],
            'note' => ['nullable', 'string'],
            'class_id' => ['nullable', 'exists:school_classes,id_kelas'],
            'subject_id' => ['nullable', 'exists:subjects,id_mapel'],
        ]);

        Attendance::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'date' => Carbon::today()->toDateString(),
            ],
            [
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
                'recorded_by' => $request->user()->id,
            ]
        );

        return redirect()->route('attendance.session', [
            'class_id' => $data['class_id'],
            'subject_id' => $data['subject_id'],
        ])->with('status', 'Absensi diperbarui untuk siswa.');
    }

    public function refreshCode(Request $request): JsonResponse
    {
        $session = AttendanceSession::where('teacher_id', $request->user()->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Sesi tidak ditemukan'], 422);
        }

        $session->update(['code' => $this->makeSessionCode($session->class, $session->subject)]);
        $session->load(['subject', 'class']);

        return response()->json([
            'code' => $session->code,
            'subject' => $session->subject?->nama_mapel,
            'class' => $session->class?->nama,
        ]);
    }

    public function scans(Request $request): JsonResponse
    {
        $teacher = $request->user();
        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');

        $session = AttendanceSession::where('teacher_id', $teacher->id)
            ->whereIn('status', ['active', 'paused'])
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->latest()
            ->first();

        if (! $session) {
            return response()->json(['data' => []]);
        }

        $class = $session->class;

        $scans = Attendance::with('student')
            ->whereDate('date', Carbon::today())
            ->when($class?->nama, function ($query, $className) {
                $query->whereHas('student.siswaProfile.kelas', function ($q) use ($className) {
                    $q->where('nama', $className);
                });
            })
            ->latest()
            ->get()
            ->map(function ($attendance) {
                return [
                    'student' => $attendance->student?->name ?? '-',
                    'status' => $attendance->status,
                    'time' => optional($attendance->created_at)->format('H:i'),
                ];
            });

        return response()->json(['data' => $scans]);
    }

    protected function makeSessionCode(?Kelas $class, ?Mapel $subject): string
    {
        $classPart = strtoupper($class?->nama ?? 'CLASS');
        $subjectPart = strtoupper($subject?->nama_mapel ?? 'MAPEL');
        $random = Str::upper(Str::random(6));

        return implode('|', array_filter([$classPart, $subjectPart, $random]));
    }
}
