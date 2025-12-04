<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Absensi Hari Ini') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('attendance.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="date" value="{{ $today->toDateString() }}">

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Tanggal</p>
                                <p class="text-lg font-semibold">{{ $today->translatedFormat('l, d F Y') }}</p>
                            </div>
                            <x-primary-button>Simpan Absensi</x-primary-button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Siswa</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Kelas</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Status</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($students as $student)
                                        @php
                                            $current = $todayAttendance[$student->id] ?? null;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2">
                                                <div class="font-semibold text-gray-800">{{ $student->name }}</div>
                                                <div class="text-gray-500 text-xs">{{ $student->identifier }}</div>
                                            </td>
                                            <td class="px-4 py-2 text-gray-700">{{ $student->classroom ?? '-' }}</td>
                                            <td class="px-4 py-2">
                                                <select name="statuses[{{ $student->id }}]" class="rounded border-gray-300 text-sm" required>
                                                    @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa', 'terlambat' => 'Terlambat'] as $value => $label)
                                                        <option value="{{ $value }}" @selected($current?->status === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="text" name="notes[{{ $student->id }}]" value="{{ old('notes.' . $student->id, $current?->note) }}" class="w-full rounded border-gray-300 text-sm" placeholder="Opsional">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>Simpan Absensi</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
