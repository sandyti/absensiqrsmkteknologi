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
        $className = $session->class?->name;

        if ($className && $student->classroom !== $className) {
            return back()->withErrors(['code' => 'Kode sesi ini tidak sesuai dengan kelas Anda.']);
        }

        $timeSlot = $session->subject?->time_slot;
        if ($timeSlot) {
            [$start, $end] = array_pad(explode('-', $timeSlot), 2, null);
            $startTime = $this->normalizeTime($start);
            $endTime = $this->normalizeTime($end);

            if ($startTime && $endTime) {
                $now = Carbon::now();
                $startDate = Carbon::createFromFormat('H:i', $startTime, $now->timezone);
                $endDate = Carbon::createFromFormat('H:i', $endTime, $now->timezone);

                if (! $now->betweenIncluded($startDate, $endDate)) {
                    return back()->withErrors(['code' => "Scan tidak sesuai jam pelajaran ($startTime - $endTime)"]);
                }
            }
        }

        Attendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'date' => Carbon::today()->toDateString(),
            ],
            [
                'status' => 'hadir',
                'note' => 'Scan QR: '.$session->subject?->name,
                'recorded_by' => $session->teacher_id,
            ]
        );

        return back()->with('status', 'Scan berhasil dicatat.');
    }

    protected function normalizeTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        // Ambil digit jam dan menit, dukung pemisah titik atau titik dua.
        $clean = preg_replace('/[^0-9]/', '', $value);
        if (strlen($clean) < 3) {
            return null;
        }

        $hours = substr($clean, 0, 2);
        $minutes = substr($clean, 2, 2) ?: '00';

        $hours = str_pad(substr($hours, 0, 2), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad(substr($minutes, 0, 2), 2, '0', STR_PAD_RIGHT);

        return "{$hours}:{$minutes}";
    }
}
