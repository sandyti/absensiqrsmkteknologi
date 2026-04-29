<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rekap Absensi
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                                            <div class="text-xs text-gray-500">{{ $record->student->siswaProfile?->kelas?->nama ?? '-' }}</div>
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
    </div>
</x-app-layout>
