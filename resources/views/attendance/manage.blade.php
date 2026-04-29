<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Presensi Hari Ini') }}
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

                    @if (! $activeSession)
                        <div class="mb-4 rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                            Belum ada sesi presensi hari ini.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('attendance.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="id_sesi" value="{{ $activeSession?->id_sesi }}">

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Tanggal</p>
                                <p class="text-lg font-semibold">{{ $today->translatedFormat('l, d F Y') }}</p>
                            </div>
                            <x-primary-button :disabled="! $activeSession">Simpan Presensi</x-primary-button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Siswa</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Kelas</th>
                                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($students as $student)
                                        @php
                                            $current = $todayPresensi[$student->id_siswa] ?? null;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2">
                                                <div class="font-semibold text-gray-800">{{ $student->nama }}</div>
                                                <div class="text-gray-500 text-xs">{{ $student->nis }}</div>
                                            </td>
                                            <td class="px-4 py-2 text-gray-700">{{ $student->kelas?->nama ?? '-' }}</td>
                                            <td class="px-4 py-2">
                                                <select name="statuses[{{ $student->id_siswa }}]" class="rounded border-gray-300 text-sm" required>
                                                    @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa', 'terlambat' => 'Terlambat'] as $value => $label)
                                                        <option value="{{ $value }}" @selected($current?->status === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button :disabled="! $activeSession">Simpan Presensi</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
