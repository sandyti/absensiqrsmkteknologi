<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Mapel;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TeacherSessionController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureGeneratedSchedules($request);

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

        $students = $selectedJadwal
            ? Siswa::with('kelas')
                ->where('id_kelas', $selectedJadwal->id_kelas)
                ->orderBy('nama')
                ->get()
            : collect();

        if ($activeSession && $selectedJadwal) {
            $this->applyFullDayLeavesToSession($activeSession, (int) $selectedJadwal->id_kelas, $request);
        }

        $fullDayLeaveStudentIds = $selectedJadwal
            ? $this->fullDayLeaveStudentIds((int) $selectedJadwal->id_kelas)
            : collect();

        $showQr = $request->boolean('show_qr');

        $scans = $activeSession
            ? Presensi::with('siswa')
                ->where('id_sesi', $activeSession->id_sesi)
                ->latest('scanned_at')
                ->get()
            : collect();

        return view('attendance.session', compact(
            'jadwals',
            'students',
            'selectedJadwalId',
            'selectedJadwal',
            'activeSession',
            'scans',
            'showQr',
            'fullDayLeaveStudentIds'
        ));
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal,id_jadwal'],
        ]);

        $jadwal = $this->accessibleJadwal($request, (int) $data['jadwal_id']);

        SesiPresensi::updateOrCreate(
            [
                'id_jadwal' => $jadwal->getKey(),
                'tanggal' => Carbon::today()->toDateString(),
            ],
            [
                'start_time' => now(),
                'end_time' => null,
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
            'jadwal_id' => ['required', 'exists:jadwal,id_jadwal'],
        ]);

        $session = SesiPresensi::where('id_jadwal', $data['jadwal_id'])
            ->where('status', 'open')
            ->whereDate('tanggal', Carbon::today())
            ->latest('id_sesi')
            ->first();

        if ($session) {
            $session->update([
                'status' => 'closed',
                'end_time' => now(),
            ]);
        }

        return redirect()->route('attendance.session', [
            'jadwal_id' => $data['jadwal_id'],
        ])->with('status', 'Sesi ditutup.');
    }

    public function markManual(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal,id_jadwal'],
            'id_siswa' => ['required', 'exists:siswa,id_siswa'],
            'status' => ['required', 'in:hadir,izin,sakit,alpa,terlambat'],
            'izin_scope' => ['nullable', 'in:session,full_day'],
        ]);

        $jadwal = $this->accessibleJadwal($request, (int) $data['jadwal_id']);
        $siswa = Siswa::findOrFail($data['id_siswa']);

        if ((int) $siswa->id_kelas !== (int) $jadwal->id_kelas) {
            return back()->withErrors(['id_siswa' => 'Siswa tidak sesuai dengan jadwal kelas.']);
        }

        $session = SesiPresensi::where('id_jadwal', $jadwal->getKey())
            ->where('status', 'open')
            ->whereDate('tanggal', Carbon::today())
            ->latest('id_sesi')
            ->first();

        if (! $session) {
            return back()->withErrors(['jadwal_id' => 'Sesi presensi belum dibuka.']);
        }

        $izinScope = $data['status'] === 'izin'
            ? ($data['izin_scope'] ?? 'session')
            : null;

        $payload = [
            'status' => $data['status'],
            'edited_by' => $request->user()->guruProfile?->id_guru,
            'scanned_at' => now(),
            'method' => 'manual',
        ];

        if ($this->hasIzinScopeColumn()) {
            $payload['izin_scope'] = $izinScope;
        }

        Presensi::updateOrCreate(
            [
                'id_sesi' => $session->id_sesi,
                'id_siswa' => $siswa->id_siswa,
            ],
            $payload
        );

        return redirect()->route('attendance.session', [
            'jadwal_id' => $jadwal->getKey(),
        ])->with('status', 'Presensi manual diperbarui.');
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal,id_jadwal'],
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
            'jadwal_id' => ['required', 'exists:jadwal,id_jadwal'],
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

        $scans = Presensi::with('siswa')
            ->where('id_sesi', $session->id_sesi)
            ->latest('scanned_at')
            ->get()
            ->map(function ($presensi) {
                return [
                    'student' => $presensi->siswa?->nama ?? '-',
                    'status' => $presensi->status,
                    'time' => $this->safeTime($presensi->scanned_at, 'H:i'),
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
            })
            ->whereExists(function ($subQuery) {
                $subQuery->selectRaw('1')
                    ->from('kelas_subject')
                    ->whereColumn('kelas_subject.id_mapel', 'jadwal.id_mapel')
                    ->whereColumn('kelas_subject.id_kelas', 'jadwal.id_kelas');
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

    protected function ensureGeneratedSchedules(Request $request): void
    {
        if (! $request->user()->isGuru()) {
            return;
        }

        $guruId = (int) $request->user()->id_ref;
        $defaultDay = Carbon::now()->locale('id')->translatedFormat('l');

        $subjects = Mapel::with('kelas')
            ->whereHas('kelas')
            ->get();

        foreach ($subjects as $subject) {
            [$startTime, $endTime] = $this->extractTimeRange($subject->jam_pelajaran);

            foreach ($subject->kelas as $kelas) {
                Jadwal::firstOrCreate(
                    [
                        'id_mapel' => $subject->id_mapel,
                        'id_kelas' => $kelas->id_kelas,
                        'id_guru' => $guruId,
                    ],
                    [
                        'hari' => $defaultDay,
                        'jam_mulai' => $startTime,
                        'jam_selesai' => $endTime,
                    ]
                );
            }
        }
    }

    protected function extractTimeRange(?string $jamPelajaran): array
    {
        if (is_string($jamPelajaran)) {
            if (preg_match('/(\d{1,2}[:.]\d{2})\s*[-–]\s*(\d{1,2}[:.]\d{2})/', $jamPelajaran, $matches)) {
                $start = str_replace('.', ':', $matches[1]).':00';
                $end = str_replace('.', ':', $matches[2]).':00';

                return [$start, $end];
            }
        }

        return ['07:00:00', '08:30:00'];
    }

    protected function safeTime(mixed $value, string $format = 'H:i:s'): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format($format);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value)->format($format);
            } catch (\Throwable) {
                return $value;
            }
        }

        return null;
    }

    protected function fullDayLeaveStudentIds(int $kelasId)
    {
        if (! $this->hasIzinScopeColumn()) {
            return collect();
        }

        return Presensi::query()
            ->join('sesi_presensi', 'sesi_presensi.id_sesi', '=', 'presensi.id_sesi')
            ->join('jadwal', 'jadwal.id_jadwal', '=', 'sesi_presensi.id_jadwal')
            ->where('jadwal.id_kelas', $kelasId)
            ->whereDate('presensi.scanned_at', Carbon::today())
            ->where('presensi.status', 'izin')
            ->where('presensi.izin_scope', 'full_day')
            ->pluck('presensi.id_siswa')
            ->unique()
            ->values();
    }

    protected function applyFullDayLeavesToSession(SesiPresensi $session, int $kelasId, Request $request): void
    {
        $fullDayLeaveStudentIds = $this->fullDayLeaveStudentIds($kelasId);

        if ($fullDayLeaveStudentIds->isEmpty()) {
            return;
        }

        $existingStudentIds = Presensi::query()
            ->where('id_sesi', $session->id_sesi)
            ->whereIn('id_siswa', $fullDayLeaveStudentIds)
            ->pluck('id_siswa');

        $missingStudentIds = $fullDayLeaveStudentIds->diff($existingStudentIds);

        if ($missingStudentIds->isEmpty()) {
            return;
        }

        $editorId = $request->user()->guruProfile?->id_guru;
        $timestamp = now();

        foreach ($missingStudentIds as $studentId) {
            Presensi::create([
                'id_sesi' => $session->id_sesi,
                'id_siswa' => $studentId,
                'status' => 'izin',
                'edited_by' => $editorId,
                'scanned_at' => $timestamp,
                'method' => 'manual',
            ] + ($this->hasIzinScopeColumn() ? ['izin_scope' => 'full_day'] : []));
        }
    }

    protected function hasIzinScopeColumn(): bool
    {
        return Schema::hasColumn('presensi', 'izin_scope');
    }
}
