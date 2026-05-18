<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

        $fullDayLeaveExists = false;
        if ($this->hasIzinScopeColumn()) {
            $fullDayLeaveExists = Presensi::query()
                ->join('sesi_presensi', 'sesi_presensi.id_sesi', '=', 'presensi.id_sesi')
                ->join('jadwal', 'jadwal.id_jadwal', '=', 'sesi_presensi.id_jadwal')
                ->where('presensi.id_siswa', $siswa->id_siswa)
                ->where('jadwal.id_kelas', $siswa->id_kelas)
                ->whereDate('presensi.scanned_at', Carbon::today())
                ->where('presensi.status', 'izin')
                ->where('presensi.izin_scope', 'full_day')
                ->exists();
        }

        if ($fullDayLeaveExists) {
            return back()->withErrors(['token' => 'Anda tercatat izin penuh untuk hari ini.']);
        }

        $payload = [
            'status' => 'hadir',
            'edited_by' => null,
            'scanned_at' => now(),
            'method' => 'scan',
        ];

        if ($this->hasIzinScopeColumn()) {
            $payload['izin_scope'] = null;
        }

        Presensi::updateOrCreate(
            [
                'id_sesi' => $session->id_sesi,
                'id_siswa' => $siswa->id_siswa,
            ],
            $payload
        );

        return back()->with('status', 'Scan berhasil dicatat.');
    }

    protected function hasIzinScopeColumn(): bool
    {
        return Schema::hasColumn('presensi', 'izin_scope');
    }
}
