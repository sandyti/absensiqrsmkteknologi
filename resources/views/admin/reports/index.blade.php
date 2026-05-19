<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Rekap Presensi
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
                <div class="flex flex-col md:flex-row">
                    <aside class="hidden md:block md:w-60 bg-gray-50 border-r border-gray-200">
                        <div class="p-4 border-b border-gray-200 text-center">
                            <div class="border rounded-lg p-3 flex justify-center">
                                <x-application-logo class="h-12 w-auto" />
                            </div>
                        </div>
                        <nav class="text-sm">
                            @if (auth()->user()->role !== 'guru')
                            <a href="{{ route('dashboard') }}" class="block px-4 py-3 hover:bg-gray-100">Home</a>
                            <a href="{{ route('teachers.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Guru</a>
                            <a href="{{ route('students.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Siswa</a>
                            <a href="{{ route('subjects.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Mapel</a>
                            @endif
                            <a href="{{ route('reports.index') }}" class="block px-4 py-3 bg-gray-200 font-semibold text-gray-800">Rekap Absensi</a>
                            @if (auth()->user()->role !== 'guru')
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-100">Logout</button>
                            </form>
                            @endif
                        </nav>
                    </aside>

                    <main class="flex-1 p-4 md:p-6">
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
                    <div class="md:col-span-4 flex justify-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Terapkan</button>
                        <a href="{{ route('reports.export', array_filter([
                            'student_id' => $filters['student_id'] ?? null,
                            'class_id' => $filters['class_id'] ?? null,
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
                                    <p class="text-sm text-gray-500">{{ $recapRows->count() }} siswa</p>
                                </div>
                                <div class="overflow-x-auto mb-6">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">No</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">NIS</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Nama</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Kelas</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Hadir</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Sakit</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Izin</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Alpha</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Terlambat</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Total Pertemuan</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Persentase Kehadiran</th>
                                                <th class="px-3 py-2 text-left font-semibold text-gray-700">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse ($recapRows as $row)
                                                <tr>
                                                    <td class="px-3 py-2">{{ $loop->iteration }}</td>
                                                    <td class="px-3 py-2">{{ $row['nis'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['nama'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['kelas'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['hadir'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['sakit'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['izin'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['alpa'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['terlambat'] }}</td>
                                                    <td class="px-3 py-2">{{ $row['total_pertemuan'] }}</td>
                                                    <td class="px-3 py-2">{{ number_format($row['persentase_kehadiran'], 2) }}%</td>
                                                    <td class="px-3 py-2">{{ $row['keterangan'] }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="12" class="px-4 py-4 text-center text-gray-500">Belum ada data rekap.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
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
