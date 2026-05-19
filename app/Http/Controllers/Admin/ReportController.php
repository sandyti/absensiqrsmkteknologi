<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->prepareReportData($request, paginate: true);

        if (auth()->user()?->isGuru()) {
            return view('guru.reports.index', $data);
        }

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

        $template = auth()->user()?->isGuru() ? 'guru.reports.pdf' : 'admin.reports.pdf';

        $html = view($template, [
            'records' => $data['records'],
            'recapRows' => $data['recapRows'],
            'recapBySubject' => $data['recapBySubject'],
            'recapByClass' => $data['recapByClass'],
            'titleRange' => $data['titleRange'],
            'statusTotals' => $data['statusTotals'],
            'showGuruDetail' => $data['showGuruDetail'],
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
     *     subjects: Collection<int, Mapel>,
     *     records: LengthAwarePaginator|Collection<int, Presensi>,
     *     recapRows: Collection<int, array<string, mixed>>,
     *     recapBySubject: Collection<int, array<string, mixed>>,
     *     recapByClass: Collection<int, array<string, mixed>>,
     *     statusTotals: array<string, int>,
     *     filters: array,
     *     titleRange: string,
     *     showGuruDetail: bool
     * }
     */
    private function prepareReportData(Request $request, bool $paginate = true): array
    {
        $students = Siswa::with('kelas')->orderBy('nama')->get();
        $classes = Kelas::orderBy('nama')->get();
        $subjects = auth()->user()?->isGuru()
            ? Mapel::whereHas('jadwals', function ($q) {
                $q->where('id_guru', auth()->user()->id_ref);
            })->orderBy('nama_mapel')->get()
            : Mapel::orderBy('nama_mapel')->get();

        $filters = $request->validate([
            'student_id' => ['nullable', 'exists:siswa,id_siswa'],
            'class_id' => ['nullable', 'exists:kelas,id_kelas'],
            'subject_id' => ['nullable', 'exists:mapel,id_mapel'],
            'range' => ['nullable', 'in:hari,minggu,bulan,semester,tahun'],
            'date' => ['nullable', 'date'],
        ]);

        $range = $filters['range'] ?? 'hari';
        $date = isset($filters['date']) ? Carbon::parse($filters['date']) : Carbon::today();

        $query = Presensi::with(['siswa.kelas', 'sesiPresensi.jadwal.mapel', 'sesiPresensi.jadwal.guru', 'editor'])
            ->when(auth()->user()?->isGuru(), function ($q) {
                $guruId = (int) auth()->user()->id_ref;
                $q->whereHas('sesiPresensi.jadwal', function ($jq) use ($guruId) {
                    $jq->where('id_guru', $guruId);
                });
            })
            ->when($filters['subject_id'] ?? null, function ($q, $subjectId) {
                $q->whereHas('sesiPresensi.jadwal', function ($jq) use ($subjectId) {
                    $jq->where('id_mapel', $subjectId);
                });
            })
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
        } elseif ($range === 'semester') {
            if ($date->month >= 7) {
                $start = Carbon::create($date->year, 7, 1)->startOfDay();
                $end = Carbon::create($date->year, 12, 31)->endOfDay();
                $label = 'Ganjil';
            } else {
                $start = Carbon::create($date->year, 1, 1)->startOfDay();
                $end = Carbon::create($date->year, 6, 30)->endOfDay();
                $label = 'Genap';
            }

            $query->whereBetween('scanned_at', [$start, $end]);
            $titleRange = 'Semester ' . $label . ' ' . $date->year;
        } else {
            $query->whereYear('scanned_at', $date->year);
            $titleRange = 'Tahun ' . $date->year;
        }

        $statusTotals = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'])
            ->groupBy('status')
            ->pluck('total', 'status');

        $allRecords = (clone $query)->get();
        $recapRows = $this->buildRecapRows($allRecords);
        $recapBySubject = $this->buildSubjectRecapRows($allRecords);
        $recapByClass = $this->buildClassRecapRows($allRecords);
        $showGuruDetail = auth()->user()?->isGuru() && $range === 'hari';

        $records = $paginate
            ? $query->orderByDesc('scanned_at')->paginate(20)->withQueryString()
            : $query->orderByDesc('scanned_at')->get();

        return [
            'students' => $students,
            'records' => $records,
            'recapRows' => $recapRows,
            'recapBySubject' => $recapBySubject,
            'recapByClass' => $recapByClass,
            'statusTotals' => [
                'hadir' => (int) ($statusTotals->get('hadir') ?? 0),
                'sakit' => (int) ($statusTotals->get('sakit') ?? 0),
                'izin' => (int) ($statusTotals->get('izin') ?? 0),
                'alpa' => (int) ($statusTotals->get('alpa') ?? 0),
                'terlambat' => (int) ($statusTotals->get('terlambat') ?? 0),
            ],
            'filters' => [
                'student_id' => $filters['student_id'] ?? null,
                'class_id' => $filters['class_id'] ?? null,
                'subject_id' => $filters['subject_id'] ?? null,
                'range' => $range,
                'date' => $date->toDateString(),
            ],
            'titleRange' => $titleRange,
            'classes' => $classes,
            'subjects' => $subjects,
            'showGuruDetail' => $showGuruDetail,
        ];
    }

    /**
     * @param Collection<int, Presensi> $records
     * @return Collection<int, array<string, mixed>>
     */
    private function buildRecapRows(Collection $records): Collection
    {
        return $records
            ->groupBy('id_siswa')
            ->map(function (Collection $studentRecords) {
                /** @var Presensi $first */
                $first = $studentRecords->first();
                $statusCount = $studentRecords->groupBy('status')->map->count();

                $hadir = (int) ($statusCount->get('hadir') ?? 0);
                $sakit = (int) ($statusCount->get('sakit') ?? 0);
                $izin = (int) ($statusCount->get('izin') ?? 0);
                $alpa = (int) ($statusCount->get('alpa') ?? 0);
                $terlambat = (int) ($statusCount->get('terlambat') ?? 0);
                $totalPertemuan = $studentRecords->count();
                $persentase = $totalPertemuan > 0 ? round(($hadir / $totalPertemuan) * 100, 2) : 0;

                return [
                    'nis' => $first->siswa?->nis ?? '-',
                    'nama' => $first->siswa?->nama ?? '-',
                    'kelas' => $first->siswa?->kelas?->nama ?? '-',
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'terlambat' => $terlambat,
                    'total_pertemuan' => $totalPertemuan,
                    'persentase_kehadiran' => $persentase,
                    'keterangan' => $this->attendanceNote($persentase),
                ];
            })
            ->sortBy([
                fn (array $row) => Str::lower($row['kelas']),
                fn (array $row) => Str::lower($row['nama']),
            ])
            ->values();
    }

    private function attendanceNote(float $percentage): string
    {
        if ($percentage >= 90) {
            return 'Sangat Baik';
        }

        if ($percentage >= 80) {
            return 'Baik';
        }

        if ($percentage >= 70) {
            return 'Cukup';
        }

        return 'Perlu Pembinaan';
    }

    /**
     * @param Collection<int, Presensi> $records
     * @return Collection<int, array<string, mixed>>
     */
    private function buildSubjectRecapRows(Collection $records): Collection
    {
        return $records
            ->groupBy(fn (Presensi $record) => $record->sesiPresensi?->jadwal?->id_mapel ?? 'unknown')
            ->map(function (Collection $subjectRecords) {
                /** @var Presensi $first */
                $first = $subjectRecords->first();
                $statusCount = $subjectRecords->groupBy('status')->map->count();

                $hadir = (int) ($statusCount->get('hadir') ?? 0);
                $izin = (int) ($statusCount->get('izin') ?? 0);
                $alpa = (int) ($statusCount->get('alpa') ?? 0);
                $pertemuan = $subjectRecords->pluck('id_sesi')->filter()->unique()->count();
                $base = max($hadir + $izin + $alpa, 1);
                $persentase = round((($hadir + $izin) / $base) * 100, 2);

                return [
                    'mapel' => $first->sesiPresensi?->jadwal?->mapel?->nama_mapel ?? '-',
                    'guru' => $first->sesiPresensi?->jadwal?->guru?->nama ?? '-',
                    'pertemuan' => $pertemuan,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'persentase_kehadiran' => $persentase,
                ];
            })
            ->sortBy(fn (array $row) => Str::lower($row['mapel']))
            ->values();
    }

    /**
     * @param Collection<int, Presensi> $records
     * @return Collection<int, array<string, mixed>>
     */
    private function buildClassRecapRows(Collection $records): Collection
    {
        return $records
            ->groupBy(fn (Presensi $record) => $record->siswa?->kelas?->id_kelas ?? 'unknown')
            ->map(function (Collection $classRecords) {
                /** @var Presensi $first */
                $first = $classRecords->first();
                $statusCount = $classRecords->groupBy('status')->map->count();

                $hadir = (int) ($statusCount->get('hadir') ?? 0);
                $izin = (int) ($statusCount->get('izin') ?? 0);
                $sakit = (int) ($statusCount->get('sakit') ?? 0);
                $terlambat = (int) ($statusCount->get('terlambat') ?? 0);
                $alpa = (int) ($statusCount->get('alpa') ?? 0);
                $pertemuan = $classRecords->pluck('id_sesi')->filter()->unique()->count();
                $jumlahSiswa = $classRecords->pluck('id_siswa')->filter()->unique()->count();
                $kehadiran = $hadir + $izin + $sakit + $terlambat;
                $base = max($kehadiran + $alpa, 1);
                $persentase = round(($kehadiran / $base) * 100, 2);

                return [
                    'kelas' => $first->siswa?->kelas?->nama ?? '-',
                    'jumlah_siswa' => $jumlahSiswa,
                    'pertemuan' => $pertemuan,
                    'kehadiran' => $kehadiran,
                    'alpa' => $alpa,
                    'persentase' => $persentase,
                ];
            })
            ->sortBy(fn (array $row) => Str::lower($row['kelas']))
            ->values();
    }
}
