<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->prepareReportData($request, paginate: true);

        return view('admin.reports.index', $data);
    }

    public function export(Request $request)
    {
        $data = $this->prepareReportData($request, paginate: false);

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $kopPath = public_path('images/kop_surat.png');
        $kopData = file_exists($kopPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath)) : null;

        $html = view('admin.reports.pdf', [
            'records' => $data['records'],
            'titleRange' => $data['titleRange'],
            'kopData' => $kopData,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rekap-presensi.pdf"',
        ]);
    }

    /**
     * @return array{
     *     students: Collection<int, Siswa>,
     *     classes: Collection<int, Kelas>,
     *     records: LengthAwarePaginator|Collection<int, Presensi>,
     *     filters: array,
     *     titleRange: string
     * }
     */
    private function prepareReportData(Request $request, bool $paginate = true): array
    {
        $students = Siswa::with('kelas')->orderBy('nama')->get();
        $classes = Kelas::orderBy('nama')->get();

        $filters = $request->validate([
            'student_id' => ['nullable', 'exists:siswa,id_siswa'],
            'class_id' => ['nullable', 'exists:kelas,id_kelas'],
            'range' => ['nullable', 'in:hari,minggu,bulan,tahun'],
            'date' => ['nullable', 'date'],
        ]);

        $range = $filters['range'] ?? 'hari';
        $date = isset($filters['date']) ? Carbon::parse($filters['date']) : Carbon::today();

        $query = Presensi::with(['siswa.kelas', 'sesiPresensi.jadwal', 'editor'])
            ->when($filters['class_id'] ?? null, function ($q, $classId) {
                $q->whereHas('siswa', function ($sq) use ($classId) {
                    $sq->where('id_kelas', $classId);
                });
            })
            ->when($filters['student_id'] ?? null, fn ($q, $studentId) => $q->where('id_siswa', $studentId));

        $titleRange = '';
        if ($range === 'hari') {
            $query->whereDate('scanned_at', $date);
            $titleRange = $date->translatedFormat('d F Y');
        } elseif ($range === 'minggu') {
            $query->whereBetween('scanned_at', [$date->copy()->startOfWeek()->startOfDay(), $date->copy()->endOfWeek()->endOfDay()]);
            $titleRange = 'Minggu ' . $date->weekOfYear;
        } elseif ($range === 'bulan') {
            $query->whereYear('scanned_at', $date->year)->whereMonth('scanned_at', $date->month);
            $titleRange = $date->translatedFormat('F Y');
        } else {
            $query->whereYear('scanned_at', $date->year);
            $titleRange = 'Tahun ' . $date->year;
        }

        $records = $paginate
            ? $query->orderByDesc('scanned_at')->paginate(20)->withQueryString()
            : $query->orderByDesc('scanned_at')->get();

        return [
            'students' => $students,
            'records' => $records,
            'filters' => [
                'student_id' => $filters['student_id'] ?? null,
                'class_id' => $filters['class_id'] ?? null,
                'range' => $range,
                'date' => $date->toDateString(),
            ],
            'titleRange' => $titleRange,
            'classes' => $classes,
        ];
    }
}
