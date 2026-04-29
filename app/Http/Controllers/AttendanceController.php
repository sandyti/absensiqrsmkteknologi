<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function manage(): RedirectResponse
    {
        return redirect()->route('attendance.session');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_sesi' => ['required', 'exists:sesi_presensi,id_sesi'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:hadir,izin,sakit,alpa,terlambat'],
        ]);

        $session = SesiPresensi::with('jadwal')->findOrFail($data['id_sesi']);
        $editorId = $request->user()->guruProfile?->id_guru;

        if ($request->user()->isGuru() && (int) $session->jadwal?->id_guru !== (int) $editorId) {
            abort(403);
        }

        if ($session->status !== 'open') {
            return back()->withErrors(['id_sesi' => 'Sesi presensi sudah ditutup.']);
        }

        foreach ($data['statuses'] as $siswaId => $status) {
            $siswa = Siswa::find($siswaId);

            if (! $siswa || (int) $siswa->id_kelas !== (int) $session->jadwal?->id_kelas) {
                continue;
            }

            Presensi::updateOrCreate(
                [
                    'id_sesi' => $session->id_sesi,
                    'id_siswa' => $siswaId,
                ],
                [
                    'status' => $status,
                    'edited_by' => $editorId,
                    'scanned_at' => now(),
                    'method' => 'manual',
                ]
            );
        }

        return redirect()->route('attendance.session', [
            'jadwal_id' => $session->id_jadwal,
        ])->with('status', 'Presensi manual berhasil disimpan.');
    }

    public function me(Request $request): View
    {
        $data = $request->validate([
            'range' => ['nullable', 'in:hari,minggu,bulan,tahun'],
            'date' => ['nullable', 'date'],
        ]);

        $range = $data['range'] ?? 'bulan';
        $anchor = isset($data['date']) ? Carbon::parse($data['date']) : Carbon::today();

        $start = $anchor->copy();
        $end = $anchor->copy();

        if ($range === 'hari') {
            // already set
        } elseif ($range === 'minggu') {
            $start = $anchor->copy()->startOfWeek();
            $end = $anchor->copy()->endOfWeek();
        } elseif ($range === 'bulan') {
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
        } else {
            $start = $anchor->copy()->startOfYear();
            $end = $anchor->copy()->endOfYear();
        }

        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }

        $records = $user
            ->presensis()
            ->with(['sesiPresensi.jadwal.kelas', 'sesiPresensi.jadwal.mapel', 'editor', 'siswa.kelas'])
            ->whereBetween('scanned_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->latest('scanned_at')
            ->paginate(15)
            ->withQueryString();

        $titleRange = match ($range) {
            'hari' => $anchor->translatedFormat('d F Y'),
            'minggu' => 'Minggu '.$anchor->weekOfYear.' ('.$start->toDateString().' - '.$end->toDateString().')',
            'bulan' => $anchor->translatedFormat('F Y'),
            default => 'Tahun '.$anchor->year,
        };

        return view('attendance.me', [
            'records' => $records,
            'range' => $range,
            'anchor' => $anchor,
            'titleRange' => $titleRange,
        ]);
    }
}
