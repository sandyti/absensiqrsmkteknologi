<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $today = Carbon::today();

        if ($user->isAdmin()) {
            $period = $request->query('period', 'hari');
            $anchorDate = $request->query('date') ? Carbon::parse($request->query('date')) : $today;
            $rangeStart = $anchorDate->copy();
            $rangeEnd = $anchorDate->copy();

            if ($period === 'minggu') {
                $rangeStart = $anchorDate->copy()->startOfWeek();
                $rangeEnd = $anchorDate->copy()->endOfWeek();
            } elseif ($period === 'bulan') {
                $rangeStart = $anchorDate->copy()->startOfMonth();
                $rangeEnd = $anchorDate->copy()->endOfMonth();
            } elseif ($period === 'tahun') {
                $rangeStart = $anchorDate->copy()->startOfYear();
                $rangeEnd = $anchorDate->copy()->endOfYear();
            }

            $summary = [
                'total_users' => User::count(),
                'total_siswa' => User::where('role', User::ROLE_SISWA)->count(),
                'total_guru' => User::where('role', User::ROLE_GURU)->count(),
                'attendances_today' => Attendance::whereDate('date', $today)->count(),
            ];

            $statusCounts = Attendance::selectRaw('status, COUNT(*) as total')
                ->whereBetween('date', [$rangeStart, $rangeEnd])
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $chartData = [
                'labels' => ['hadir', 'izin', 'sakit', 'alpa', 'terlambat'],
                'data' => [
                    $statusCounts['hadir'] ?? 0,
                    $statusCounts['izin'] ?? 0,
                    $statusCounts['sakit'] ?? 0,
                    $statusCounts['alpa'] ?? 0,
                    $statusCounts['terlambat'] ?? 0,
                ],
                'period_label' => $period,
                'range_start' => $rangeStart->toDateString(),
                'range_end' => $rangeEnd->toDateString(),
            ];

            return view('dashboard', compact('user', 'summary', 'today', 'chartData', 'period', 'anchorDate'));
        }

        if ($user->isGuru()) {
            $students = User::where('role', User::ROLE_SISWA)->orderBy('name')->get();
            $todayAttendance = Attendance::whereDate('date', $today)->get()->keyBy('student_id');

            return view('dashboard', compact('user', 'students', 'today', 'todayAttendance'));
        }

        $records = $user->attendances()->latest('date')->limit(10)->get();
        $todayRecord = $user->attendances()->whereDate('date', $today)->first();

        return view('dashboard', compact('user', 'records', 'today', 'todayRecord'));
    }
}
