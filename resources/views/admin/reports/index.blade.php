<x-app-layout>
    <x-slot name="header">
        @if (auth()->user()->isAdmin())
            <div>
                <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Rekap Absensi
                </h2>
            </div>
        @else
            <div class="flex items-center gap-2">
                <a href="{{ url()->previous() }}" class="text-gray-700 text-xl">&larr;</a>
                <div class="text-lg font-semibold text-gray-800">Logo</div>
            </div>
        @endif
    </x-slot>

    <div class="py-6">
        @if (auth()->user()->isAdmin())
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
                                <a href="{{ route('teachers.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Guru</a>
                                <a href="{{ route('students.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Siswa</a>
                                <a href="{{ route('subjects.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Mapel Dan Jam Pelajaran</a>
                                <a href="{{ route('reports.index') }}" class="block px-4 py-3 bg-gray-200 font-semibold text-gray-800">Rekap Absensi</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-100">Logout</button>
                                </form>
                            </nav>
                        </aside>

                        <main class="flex-1 p-6">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">REKAP ABSENSI</h3>
                                <p class="text-sm text-gray-500">By SMK TEKNOLOGI KOTAWARINGIN</p>
                            </div>

                            <div class="bg-gray-200 p-4 rounded-lg">
                                <div class="bg-white p-4 rounded-lg border border-gray-300 space-y-4">
                                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Per Mapel</label>
                                            <select name="subject_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                                                <option value="">Semua</option>
                                                @foreach ($subjects as $subject)
                                                    <option value="{{ $subject->id }}" @selected($filters['subject_id'] == $subject->id)>{{ $subject->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Per Siswa</label>
                                            <select name="student_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                                                <option value="">Semua</option>
                                                @foreach ($students as $student)
                                                    <option value="{{ $student->id }}" @selected($filters['student_id'] == $student->id)>{{ $student->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Per Kelas</label>
                                            <select name="class_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                                                <option value="">Semua</option>
                                                @foreach ($classes as $class)
                                                    <option value="{{ $class->id }}" @selected($filters['class_id'] == $class->id)>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Periode</label>
                                            <select name="range" class="mt-1 w-full rounded border-gray-300 text-sm">
                                                <option value="hari" @selected($filters['range'] === 'hari')>Per Hari</option>
                                                <option value="minggu" @selected($filters['range'] === 'minggu')>Per Minggu</option>
                                                <option value="bulan" @selected($filters['range'] === 'bulan')>Per Bulan</option>
                                                <option value="tahun" @selected($filters['range'] === 'tahun')>Per Tahun</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Tanggal Acuan</label>
                                            <input type="date" name="date" value="{{ $filters['date'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                                        </div>
                                        <div class="md:col-span-4 flex justify-end space-x-2">
                                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Terapkan</button>
                                            <button type="button" id="open-export-modal" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-semibold text-gray-800 hover:bg-gray-50">
                                                Export PDF/EXCEL
                                            </button>
                                        </div>
                                    </form>

                                    <div id="export-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm">
                                        <div class="bg-white rounded-xl shadow-2xl border border-gray-200 w-full max-w-md mx-4 overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="export-modal-title">
                                            <div class="flex items-start justify-between px-5 py-4 border-b border-gray-200">
                                                <div>
                                                    <p class="text-xs uppercase tracking-wide text-gray-500">Rekap Absensi</p>
                                                    <h4 id="export-modal-title" class="text-lg font-semibold text-gray-900">Export Data</h4>
                                                    <p class="text-sm text-gray-500">Unduh laporan sesuai format yang dibutuhkan.</p>
                                                </div>
                                                <button type="button" id="close-export-modal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                                    <span class="sr-only">Tutup modal</span>
                                                    ✕
                                                </button>
                                            </div>
                                            <div class="p-5 space-y-4">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <a href="{{ route('reports.export', array_filter([
                                                        'subject_id' => $filters['subject_id'] ?? null,
                                                        'student_id' => $filters['student_id'] ?? null,
                                                        'class_id' => $filters['class_id'] ?? null,
                                                        'range' => $filters['range'] ?? null,
                                                        'date' => $filters['date'] ?? null,
                                                    ])) }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition">
                                                        <span>Export PDF</span>
                                                    </a>
                                                    <a href="{{ request()->fullUrlWithQuery(array_filter(['export' => 'excel'])) }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700 transition">
                                                        <span>Export Excel</span>
                                                    </a>
                                                </div>
                                                <p class="text-xs text-gray-500 text-center">Pastikan filter sudah sesuai sebelum melakukan export.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-300 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <p class="text-sm text-gray-500">Rentang</p>
                                                <p class="text-lg font-semibold text-gray-800">{{ $titleRange }}</p>
                                            </div>
                                            <p class="text-sm text-gray-500">{{ $records->total() }} data</p>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Tanggal</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Siswa</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Status</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Dicatat Oleh</th>
                                                        <th class="px-4 py-2 text-left font-semibold text-gray-700">Catatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @forelse ($records as $record)
                                                        <tr>
                                                            <td class="px-4 py-2">{{ $record->date->translatedFormat('d F Y') }}</td>
                                                            <td class="px-4 py-2">
                                                                <div class="font-semibold text-gray-800">{{ $record->student->name ?? '-' }}</div>
                                                                <div class="text-xs text-gray-500">{{ $record->student->classroom ?? '-' }}</div>
                                                            </td>
                                                            <td class="px-4 py-2 capitalize font-semibold">{{ $record->status }}</td>
                                                            <td class="px-4 py-2 text-gray-700">{{ $record->recorder->name ?? '-' }}</td>
                                                            <td class="px-4 py-2 text-gray-700">{{ $record->note ?? '-' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada data absensi.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-3">
                                            {{ $records->links() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.getElementById('export-modal');
                    const openBtn = document.getElementById('open-export-modal');
                    const closeBtn = document.getElementById('close-export-modal');
                    if (!modal || !openBtn || !closeBtn) return;

                    const closeModal = () => modal.classList.add('hidden');
                    const openModal = () => modal.classList.remove('hidden');

                    openBtn.addEventListener('click', openModal);
                    closeBtn.addEventListener('click', closeModal);
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) closeModal();
                    });
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                            closeModal();
                        }
                    });
                });
            </script>
        @else
            <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4">
                <div class="text-center">
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">REKAP ABSENSI</h3>
                </div>

                <form method="GET" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Per Mapel</label>
                        <select name="subject_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="">Semua</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected($filters['subject_id'] == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Per Siswa</label>
                        <select name="student_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="">Semua</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected($filters['student_id'] == $student->id)>{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Per Kelas</label>
                        <select name="class_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="">Semua</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected($filters['class_id'] == $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Periode</label>
                        <select name="range" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="hari" @selected($filters['range'] === 'hari')>Per Hari</option>
                            <option value="minggu" @selected($filters['range'] === 'minggu')>Per Minggu</option>
                            <option value="bulan" @selected($filters['range'] === 'bulan')>Per Bulan</option>
                            <option value="tahun" @selected($filters['range'] === 'tahun')>Per Tahun</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Acuan</label>
                        <input type="date" name="date" value="{{ $filters['date'] }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Terapkan</button>
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
                    <div class="space-y-3 max-h-80 overflow-y-auto text-sm text-gray-800">
                        @forelse ($records as $record)
                            <div class="border border-gray-200 rounded-md p-2">
                                <div class="font-semibold">{{ $record->student->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $record->student->classroom ?? '-' }} · {{ $record->date->translatedFormat('d F Y') }}</div>
                                <div class="capitalize font-semibold mt-1">{{ $record->status }}</div>
                                <div class="text-xs text-gray-500">Dicatat: {{ $record->recorder->name ?? '-' }}</div>
                                @if($record->note)
                                    <div class="text-xs text-gray-600 mt-1">Catatan: {{ $record->note }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-gray-500">Belum ada data absensi.</p>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        {{ $records->links() }}
                    </div>
                </div>

                <div class="text-center">
                    <button type="button" id="open-export-modal-guru" class="px-4 py-2 border border-gray-400 rounded-md text-sm font-semibold hover:bg-gray-50">
                        Export PDF/EXCEL
                    </button>
                </div>

                <div id="export-modal-guru" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm">
                    <div class="bg-white rounded-xl shadow-2xl border border-gray-200 w-full max-w-md mx-4 overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="export-modal-title-guru">
                        <div class="flex items-start justify-between px-5 py-4 border-b border-gray-200">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Rekap Absensi</p>
                                <h4 id="export-modal-title-guru" class="text-lg font-semibold text-gray-900">Export Data</h4>
                                <p class="text-sm text-gray-500">Unduh laporan sesuai format yang dibutuhkan.</p>
                            </div>
                            <button type="button" id="close-export-modal-guru" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <span class="sr-only">Tutup modal</span>
                                ✕
                            </button>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <a href="{{ route('reports.export', array_filter([
                                    'subject_id' => $filters['subject_id'] ?? null,
                                    'student_id' => $filters['student_id'] ?? null,
                                    'class_id' => $filters['class_id'] ?? null,
                                    'range' => $filters['range'] ?? null,
                                    'date' => $filters['date'] ?? null,
                                ])) }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-red-600 text-white font-semibold shadow hover:bg-red-700 transition">
                                    <span>Export PDF</span>
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(array_filter(['export' => 'excel'])) }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700 transition">
                                    <span>Export Excel</span>
                                </a>
                            </div>
                            <p class="text-xs text-gray-500 text-center">Pastikan filter sudah sesuai sebelum melakukan export.</p>
                        </div>
                    </div>
                </div>

                <div class="text-center text-xs text-gray-500 border-t border-gray-200 pt-3">
                    By SMK TEKNOLOGI KOTAWARINGIN
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.getElementById('export-modal-guru');
                    const openBtn = document.getElementById('open-export-modal-guru');
                    const closeBtn = document.getElementById('close-export-modal-guru');
                    if (!modal || !openBtn || !closeBtn) return;

                    const closeModal = () => modal.classList.add('hidden');
                    const openModal = () => modal.classList.remove('hidden');

                    openBtn.addEventListener('click', openModal);
                    closeBtn.addEventListener('click', closeModal);
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) closeModal();
                    });
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                            closeModal();
                        }
                    });
                });
            </script>
        @endif
    </div>
</x-app-layout>
