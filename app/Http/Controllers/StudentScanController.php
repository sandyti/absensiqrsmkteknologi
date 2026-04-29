<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentScanController extends Controller
{
    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $session = AttendanceSession::with(['class', 'subject', 'teacher'])
            ->where('status', 'active')
            ->get()
            ->first(function ($session) use ($data) {
                return $session->code === $data['code'];
            });

        if (! $session) {
            return back()->withErrors(['code' => 'Sesi tidak ditemukan atau sudah ditutup.']);
        }

        $student = $request->user();
        $className = $session->class?->nama;
        $studentClassName = $student->siswaProfile?->kelas?->nama;

        if ($className && $studentClassName !== $className) {
            return back()->withErrors(['code' => 'Kode sesi ini tidak sesuai dengan kelas Anda.']);
        }

        Attendance::create([
            'student_id' => $student->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'hadir',
            'note' => 'Scan QR: '.$session->subject?->nama_mapel,
            'recorded_by' => $session->teacher_id,
        ]);

        return back()->with('status', 'Scan berhasil dicatat.');
    }

    protected function normalizeTime(?string $value): ?string
    {
        return null;
    }
}
