<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Laporan Presensi Guru
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
                <div class="flex">
                    <aside class="w-60 bg-gray-50 border-r border-gray-200">
                        <div class="p-4 border-b border-gray-200 text-center">
                            <div class="border rounded-lg p-3 flex justify-center">
                                <x-application-logo class="h-12 w-auto" />
                            </div>
                        </div>
                        <nav class="text-sm">
                            <a href="{{ route('dashboard') }}" class="block px-4 py-3 hover:bg-gray-100">Home</a>
                            <a href="{{ route('attendance.session') }}" class="block px-4 py-3 hover:bg-gray-100">Sesi Presensi</a>
                            <a href="{{ route('reports.index') }}" class="block px-4 py-3 bg-gray-200 font-semibold text-gray-800">Laporan Presensi</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-100">Logout</button>
                            </form>
                        </nav>
                    </aside>

                    <main class="flex-1 p-6">
                        <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-4">
                            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Per Siswa</label>
                                    <input
                                        type="text"
                                        id="student_search"
                                        list="student_options"
                                        class="mt-1 w-full rounded border-gray-300 text-sm"
                                        placeholder="Cari nama siswa..."
                                        value="{{ optional($students->firstWhere('id_siswa', $filters['student_id']))?->nama }}"
                                    >
                                    <input type="hidden" name="student_id" id="student_id" value="{{ $filters['student_id'] ?? '' }}">
                                    <datalist id="student_options">
                                        <option value="Semua" data-id=""></option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->nama }}" data-id="{{ $student->id_siswa }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Per Kelas</label>
                                    <select name="class_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                                        <option value="">Semua</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id_kelas }}" @selected($filters['class_id'] == $class->id_kelas)>{{ $class->nama }} - {{ $class->tingkat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Periode</label>
                                    <select name="range" class="mt-1 w-full rounded border-gray-300 text-sm">
                                        <option value="hari" @selected($filters['range'] === 'hari')>Per Hari</option>
                                        <option value="minggu" @selected($filters['range'] === 'minggu')>Per Minggu</option>
                                        <option value="bulan" @selected($filters['range'] === 'bulan')>Per Bulan</option>
                                        <option value="semester" @selected($filters['range'] === 'semester')>Per Semester</option>
                                        <option value="tahun" @selected($filters['range'] === 'tahun')>Per Tahun</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal Acuan</label>
                                    <input type="date" name="date" value="{{ $filters['date'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Per Mapel</label>
                                    <select name="subject_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                                        <option value="">Semua</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id_mapel }}" @selected($filters['subject_id'] == $subject->id_mapel)>{{ $subject->nama_mapel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-4 flex justify-end gap-2">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Terapkan</button>
                                    <a href="{{ route('reports.export', array_filter([
                                        'student_id' => $filters['student_id'] ?? null,
                                        'class_id' => $filters['class_id'] ?? null,
                                        'subject_id' => $filters['subject_id'] ?? null,
                                        'range' => $filters['range'] ?? null,
                                        'date' => $filters['date'] ?? null,
                                    ])) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-semibold text-gray-800 hover:bg-gray-50">
                                        Export PDF
                                    </a>
                                </div>
                            </form>

                            <div class="border border-gray-300 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <p class="text-sm text-gray-500">Rentang</p>
                                        <p class="text-lg font-semibold text-gray-800">{{ $titleRange }}</p>
                                    </div>
                                    <p class="text-sm text-gray-500">{{ $records->total() }} data</p>
                                </div>
                                @if ($showGuruDetail)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Waktu</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Siswa</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Kelas</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Mapel</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Status</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Metode</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Guru</th>
                                                    <th class="px-4 py-2 text-left font-semibold text-gray-700">Diubah Oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @forelse ($records as $record)
                                                    <tr>
                                                        <td class="px-4 py-2">{{ $record->scanned_at?->translatedFormat('d F Y H:i') ?? '-' }}</td>
                                                        <td class="px-4 py-2">{{ $record->siswa?->nama ?? '-' }}</td>
                                                        <td class="px-4 py-2">{{ $record->siswa?->kelas?->nama ?? '-' }}</td>
                                                        <td class="px-4 py-2">{{ $record->sesiPresensi?->jadwal?->mapel?->nama_mapel ?? '-' }}</td>
                                                        <td class="px-4 py-2 capitalize font-semibold">{{ $record->status }}</td>
                                                        <td class="px-4 py-2 capitalize text-gray-700">{{ $record->method }}</td>
                                                        <td class="px-4 py-2">{{ $record->sesiPresensi?->jadwal?->guru?->nama ?? '-' }}</td>
                                                        <td class="px-4 py-2 text-gray-700">{{ $record->editor?->nama ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">Belum ada data presensi.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $records->links() }}
                                    </div>
                                @else
                                    <div class="space-y-6">
                                        <div class="overflow-x-auto">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Rekap Per Siswa</p>
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Siswa</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Kelas</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Hadir</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Sakit</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Izin</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Alpha</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Terlambat</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">% Hadir</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @forelse ($recapRows as $row)
                                                        <tr>
                                                            <td class="px-4 py-2">{{ $row['nama'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['kelas'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['hadir'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['sakit'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['izin'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['alpa'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['terlambat'] }}</td>
                                                            <td class="px-4 py-2">{{ number_format($row['persentase_kehadiran'], 2) }}%</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="px-4 py-4 text-center text-gray-500">Belum ada data rekap siswa.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Rekap Per Mapel</p>
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Mapel</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Guru</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Pertemuan</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Hadir</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Izin</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Alpha</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">% Kehadiran</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @forelse ($recapBySubject as $row)
                                                        <tr>
                                                            <td class="px-4 py-2">{{ $row['mapel'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['guru'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['pertemuan'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['hadir'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['izin'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['alpa'] }}</td>
                                                            <td class="px-4 py-2">{{ number_format($row['persentase_kehadiran'], 2) }}%</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="px-4 py-4 text-center text-gray-500">Belum ada data rekap mapel.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Rekap Per Kelas</p>
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Kelas</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Jumlah Siswa</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Pertemuan</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Kehadiran</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Alpha</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">%</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @forelse ($recapByClass as $row)
                                                        <tr>
                                                            <td class="px-4 py-2">{{ $row['kelas'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['jumlah_siswa'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['pertemuan'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['kehadiran'] }}</td>
                                                            <td class="px-4 py-2">{{ $row['alpa'] }}</td>
                                                            <td class="px-4 py-2">{{ number_format($row['persentase'], 2) }}%</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada data rekap kelas.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const searchInput = document.getElementById('student_search');
            const studentIdInput = document.getElementById('student_id');
            const options = Array.from(document.querySelectorAll('#student_options option'));

            if (!searchInput || !studentIdInput || options.length === 0) {
                return;
            }

            const syncStudentId = () => {
                const keyword = (searchInput.value || '').trim().toLowerCase();
                const match = options.find((option) => option.value.trim().toLowerCase() === keyword);
                studentIdInput.value = match ? (match.dataset.id || '') : '';
            };

            searchInput.addEventListener('change', syncStudentId);
            searchInput.addEventListener('blur', syncStudentId);
        })();
    </script>
</x-app-layout>
