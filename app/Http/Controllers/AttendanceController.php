<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function manage(): View
    {
        $today = Carbon::today();
        $students = User::where('role', User::ROLE_SISWA)->orderBy('name')->get();
        $todayAttendance = Attendance::whereDate('date', $today)->get()->keyBy('student_id');

        return view('attendance.manage', compact('students', 'today', 'todayAttendance'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:hadir,izin,sakit,alpa,terlambat'],
            'notes' => ['array'],
            'notes.*' => ['nullable', 'string'],
        ]);

        foreach ($data['statuses'] as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $data['date'],
                ],
                [
                    'status' => $status,
                    'note' => $data['notes'][$studentId] ?? null,
                    'recorded_by' => $request->user()->id,
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
            ->attendances()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->latest('date')
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
