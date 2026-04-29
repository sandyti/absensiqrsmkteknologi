<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Presensi;
use App\Models\Siswa;
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
                'total_siswa' => Siswa::count(),
                'total_guru' => Guru::count(),
                'presensis_today' => Presensi::whereDate('scanned_at', $today)->count(),
            ];

            $statusCounts = Presensi::selectRaw('status, COUNT(*) as total')
                ->whereBetween('scanned_at', [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay()])
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
            $students = Siswa::with('kelas')->orderBy('nama')->get();
            $todayPresensi = Presensi::whereDate('scanned_at', $today)->get()->keyBy('id_siswa');

            return view('dashboard', compact('user', 'students', 'today', 'todayPresensi'));
        }

        $records = $user->presensis()->with(['sesiPresensi.jadwal.mapel', 'sesiPresensi.jadwal.kelas'])->latest('scanned_at')->limit(10)->get();
        $todayRecord = $user->presensis()->whereDate('scanned_at', $today)->first();

        return view('dashboard', compact('user', 'records', 'today', 'todayRecord'));
    }
}
