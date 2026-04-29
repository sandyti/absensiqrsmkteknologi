<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\Siswa;
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

        $session = SesiPresensi::with(['jadwal.kelas', 'jadwal.mapel', 'jadwal.guru'])
            ->where('status', 'open')
            ->whereDate('tanggal', Carbon::today())
            ->where('token', $data['token'])
            ->first();

        if (! $session) {
            return back()->withErrors(['token' => 'Sesi tidak ditemukan atau sudah ditutup.']);
        }

        $siswa = Siswa::find($request->user()->id_ref);

        if (! $siswa) {
            return back()->withErrors(['token' => 'Data siswa tidak ditemukan.']);
        }

        if ((int) $siswa->id_kelas !== (int) $session->jadwal?->id_kelas) {
            return back()->withErrors(['token' => 'Token sesi ini tidak sesuai dengan kelas Anda.']);
        }

        Presensi::updateOrCreate(
            [
                'id_sesi' => $session->id_sesi,
                'id_siswa' => $siswa->id_siswa,
            ],
            [
                'status' => 'hadir',
                'edited_by' => null,
                'scanned_at' => now(),
                'method' => 'scan',
            ]
        );

        return back()->with('status', 'Scan berhasil dicatat.');
    }
}
