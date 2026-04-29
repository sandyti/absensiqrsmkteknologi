<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\Siswa;
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

        $students = $selectedJadwal
            ? Siswa::with('kelas')
                ->where('id_kelas', $selectedJadwal->id_kelas)
                ->orderBy('nama')
                ->get()
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
            'showQr'
        ));
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id_jadwal'],
        ]);

        $jadwal = $this->accessibleJadwal($request, (int) $data['jadwal_id']);

        SesiPresensi::updateOrCreate(
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
            'id_siswa' => ['required', 'exists:siswas,id_siswa'],
            'status' => ['required', 'in:hadir,izin,sakit,alpa,terlambat'],
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

        Presensi::updateOrCreate(
            [
                'id_sesi' => $session->id_sesi,
                'id_siswa' => $siswa->id_siswa,
            ],
            [
                'status' => $data['status'],
                'edited_by' => $request->user()->guruProfile?->id_guru,
                'scanned_at' => now(),
                'method' => 'manual',
            ]
        );

        return redirect()->route('attendance.session', [
            'jadwal_id' => $jadwal->getKey(),
        ])->with('status', 'Presensi manual diperbarui.');
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

        $scans = Presensi::with('siswa')
            ->where('id_sesi', $session->id_sesi)
            ->latest('scanned_at')
            ->get()
            ->map(function ($presensi) {
                return [
                    'student' => $presensi->siswa?->nama ?? '-',
                    'status' => $presensi->status,
                    'time' => optional($presensi->scanned_at)->format('H:i'),
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
