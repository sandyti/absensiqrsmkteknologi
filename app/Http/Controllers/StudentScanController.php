<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SesiPresensi;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentScanController extends Controller
{
    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $session = SesiPresensi::with(['jadwal.kelas', 'jadwal.mapel', 'jadwal.guru.user'])
            ->where('status', 'open')
            ->whereDate('tanggal', Carbon::today())
            ->where('token', $data['token'])
            ->first();

        if (! $session) {
            return back()->withErrors(['token' => 'Sesi tidak ditemukan atau sudah ditutup.']);
        }

        $student = $request->user();
        $classId = $session->jadwal?->id_kelas;
        $studentClassId = $student->siswaProfile?->id_kelas;

        if ($classId && (int) $studentClassId !== (int) $classId) {
            return back()->withErrors(['token' => 'Token sesi ini tidak sesuai dengan kelas Anda.']);
        }

        Attendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date' => Carbon::today()->toDateString(),
            ],
            [
                'status' => 'hadir',
                'note' => 'Scan QR: ' . ($session->jadwal?->mapel?->nama_mapel ?? '-'),
                'recorded_by' => $session->jadwal?->guru?->user?->id,
            ]
        );

        return back()->with('status', 'Scan berhasil dicatat.');
    }
}
