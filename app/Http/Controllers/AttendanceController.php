<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function manage(): View
    {
        $today = Carbon::today();
        $students = Siswa::with('kelas')->orderBy('nama')->get();
        $activeSession = SesiPresensi::whereDate('tanggal', $today)->latest('id_sesi')->first();
        $todayPresensi = $activeSession
            ? Presensi::with('siswa')
                ->where('id_sesi', $activeSession->id_sesi)
                ->get()
                ->keyBy('id_siswa')
            : collect();

        return view('attendance.manage', compact('students', 'today', 'todayPresensi', 'activeSession'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_sesi' => ['required', 'exists:sesi_presensis,id_sesi'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:hadir,izin,sakit,alpa,terlambat'],
        ]);

        $editorId = $request->user()->guruProfile?->id_guru;

        foreach ($data['statuses'] as $siswaId => $status) {
            Presensi::updateOrCreate(
                [
                    'id_sesi' => $data['id_sesi'],
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

        return back()->with('status', 'Absensi berhasil disimpan.');
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

        $records = auth()->user()
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
