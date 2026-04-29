<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Jadwal;
use App\Models\SesiPresensi;
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
        $jadwals = $this->jadwalQuery($request)
            ->with(['kelas', 'mapel', 'guru'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $selectedJadwalId = $request->query('jadwal_id');
        $selectedJadwal = $selectedJadwalId
            ? $jadwals->firstWhere('id_jadwal', (int) $selectedJadwalId)
            : null;

        $activeSession = $selectedJadwal
            ? SesiPresensi::where('id_jadwal', $selectedJadwal->getKey())
                ->where('status', 'open')
                ->whereDate('tanggal', Carbon::today())
                ->latest('id_sesi')
                ->first()
            : null;

        $students = collect();
        if ($selectedJadwal?->kelas) {
            $students = User::where('role', User::ROLE_SISWA)
                ->whereHas('siswaProfile', function ($query) use ($selectedJadwal) {
                    $query->where('id_kelas', $selectedJadwal->id_kelas);
                })
                ->orderBy('username')
                ->get();
        }

        $showQr = $request->boolean('show_qr');

        $scans = collect();
        if ($activeSession && $selectedJadwal?->kelas) {
            $scans = Attendance::with('student')
                ->whereDate('date', Carbon::today())
                ->whereHas('student.siswaProfile', function ($query) use ($selectedJadwal) {
                    $query->where('id_kelas', $selectedJadwal->id_kelas);
                })
                ->latest()
                ->get();
        }

        return view('attendance.session', compact(
            'jadwals',
            'students',
            'selectedJadwalId',
            'selectedJadwal',
            'activeSession',
            'scans',
            'showQr'
        ));
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id_jadwal'],
        ]);

        $jadwal = $this->accessibleJadwal($request, (int) $data['jadwal_id']);

        $session = SesiPresensi::updateOrCreate(
            [
                'id_jadwal' => $jadwal->getKey(),
                'tanggal' => Carbon::today()->toDateString(),
            ],
            [
                'token' => $this->makeToken($jadwal),
                'status' => 'open',
            ]
        );

        return redirect()->route('attendance.session', [
            'jadwal_id' => $jadwal->getKey(),
            'show_qr' => 1,
        ])->with('status', 'Sesi presensi dimulai.');
    }

    public function close(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id_jadwal'],
        ]);

        $session = SesiPresensi::where('id_jadwal', $data['jadwal_id'])
            ->where('status', 'open')
            ->whereDate('tanggal', Carbon::today())
            ->latest('id_sesi')
            ->first();

        if ($session) {
            $session->update(['status' => 'closed']);
        }

        return redirect()->route('attendance.session', [
            'jadwal_id' => $data['jadwal_id'],
        ])->with('status', 'Sesi ditutup.');
    }

    public function markManual(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id_jadwal'],
            'student_id' => ['required', 'exists:users,id'],
            'status' => ['required', 'in:hadir,izin,sakit,alpa,terlambat'],
            'note' => ['nullable', 'string'],
        ]);

        $jadwal = $this->accessibleJadwal($request, (int) $data['jadwal_id']);
        $student = User::where('role', User::ROLE_SISWA)->findOrFail($data['student_id']);

        if ((int) $student->siswaProfile?->id_kelas !== (int) $jadwal->id_kelas) {
            return back()->withErrors(['student_id' => 'Siswa tidak sesuai dengan jadwal kelas.']);
        }

        Attendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date' => Carbon::today()->toDateString(),
            ],
            [
                'status' => $data['status'],
                'note' => $data['note'] ?? null,
                'recorded_by' => $request->user()->id,
            ]
        );

        return redirect()->route('attendance.session', [
            'jadwal_id' => $jadwal->getKey(),
        ])->with('status', 'Absensi manual diperbarui.');
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id_jadwal'],
        ]);

        $jadwal = $this->accessibleJadwal($request, (int) $data['jadwal_id']);

        $session = SesiPresensi::where('id_jadwal', $jadwal->getKey())
            ->where('status', 'open')
            ->whereDate('tanggal', Carbon::today())
            ->latest('id_sesi')
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Sesi tidak ditemukan'], 422);
        }

        $session->update([
            'token' => $this->makeToken($jadwal),
        ]);

        return response()->json([
            'token' => $session->token,
        ]);
    }

    public function scans(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id_jadwal'],
        ]);

        $jadwal = $this->accessibleJadwal($request, (int) $data['jadwal_id']);

        $session = SesiPresensi::where('id_jadwal', $jadwal->getKey())
            ->where('status', 'open')
            ->whereDate('tanggal', Carbon::today())
            ->latest('id_sesi')
            ->first();

        if (! $session) {
            return response()->json(['data' => []]);
        }

        $scans = Attendance::with('student')
            ->whereDate('date', Carbon::today())
            ->whereHas('student.siswaProfile', function ($query) use ($jadwal) {
                $query->where('id_kelas', $jadwal->id_kelas);
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

    protected function accessibleJadwal(Request $request, int $jadwalId): Jadwal
    {
        return $this->jadwalQuery($request)
            ->whereKey($jadwalId)
            ->firstOrFail();
    }

    protected function jadwalQuery(Request $request)
    {
        return Jadwal::query()
            ->when($request->user()->isGuru(), function ($query) use ($request) {
                $query->where('id_guru', $request->user()->id_ref);
            });
    }

    protected function makeToken(Jadwal $jadwal): string
    {
        return implode('-', [
            'JADWAL',
            $jadwal->getKey(),
            Str::upper(Str::random(8)),
        ]);
    }
}
