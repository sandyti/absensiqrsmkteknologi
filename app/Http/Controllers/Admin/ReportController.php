<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
            'Content-Disposition' => 'inline; filename="rekap-absensi.pdf"',
        ]);
    }

    /**
     * Prepare data and query for report listing/export.
     *
     * @return array{
     *     subjects: Collection<int, Subject>,
     *     students: Collection<int, User>,
     *     classes: Collection<int, SchoolClass>,
     *     records: LengthAwarePaginator|Collection<int, Attendance>,
     *     filters: array,
     *     titleRange: string
     * }
     */
    private function prepareReportData(Request $request, bool $paginate = true): array
    {
        $user = $request->user();

        if ($user->isGuru()) {
            $subjects = Subject::where('teacher_id', $user->id)->orderBy('name')->get();
            $classes = SchoolClass::whereIn('id', $subjects->pluck('class_id')->filter())->orderBy('name')->get();
            $classNames = $classes->pluck('name');
            $students = User::where('role', User::ROLE_SISWA)
                ->when($classNames->isNotEmpty(), fn ($q) => $q->whereIn('classroom', $classNames))
                ->orderBy('name')
                ->get();
        } else {
            $subjects = Subject::orderBy('name')->get();
            $students = User::where('role', User::ROLE_SISWA)->orderBy('name')->get();
            $classes = SchoolClass::orderBy('name')->get();
            $classNames = $classes->pluck('name');
        }

        $filters = $request->validate([
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'student_id' => ['nullable', 'exists:users,id'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
            'range' => ['nullable', 'in:hari,minggu,bulan,tahun'],
            'date' => ['nullable', 'date'],
        ]);

        $range = $filters['range'] ?? 'hari';
        $date = isset($filters['date']) ? Carbon::parse($filters['date']) : Carbon::today();

        $query = Attendance::with(['student', 'recorder'])
            ->when($filters['subject_id'] ?? null, function ($q, $subjectId) {
                $q->whereHas('student', function ($sq) use ($subjectId) {
                    $sq->whereHas('subjects', function ($ssq) use ($subjectId) {
                        $ssq->where('subjects.id', $subjectId);
                    });
                });
            })
            ->when($filters['class_id'] ?? null, function ($q, $classId) {
                $q->whereHas('student', function ($sq) use ($classId) {
                    $sq->where('classroom', SchoolClass::find($classId)?->name);
                });
            })
            ->when($filters['student_id'] ?? null, fn ($q, $studentId) => $q->where('student_id', $studentId));

        if ($user->isGuru() && isset($classNames) && $classNames->isNotEmpty()) {
            $query->whereHas('student', function ($sq) use ($classNames) {
                $sq->whereIn('classroom', $classNames);
            });
        }

        $titleRange = '';
        if ($range === 'hari') {
            $query->whereDate('date', $date);
            $titleRange = $date->translatedFormat('d F Y');
        } elseif ($range === 'minggu') {
            $query->whereBetween('date', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()]);
            $titleRange = 'Minggu ' . $date->weekOfYear;
        } elseif ($range === 'bulan') {
            $query->whereYear('date', $date->year)->whereMonth('date', $date->month);
            $titleRange = $date->translatedFormat('F Y');
        } else {
            $query->whereYear('date', $date->year);
            $titleRange = 'Tahun ' . $date->year;
        }

        $records = $paginate
            ? $query->orderByDesc('date')->paginate(20)->withQueryString()
            : $query->orderByDesc('date')->get();

        return [
            'subjects' => $subjects,
            'students' => $students,
            'records' => $records,
            'filters' => [
                'subject_id' => $filters['subject_id'] ?? null,
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
