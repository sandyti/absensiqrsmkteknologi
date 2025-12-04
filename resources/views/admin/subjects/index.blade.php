<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Admin
            </h2>
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
                            <a href="{{ route('teachers.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Guru</a>
                            <a href="{{ route('students.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Siswa</a>
                            <a href="{{ route('subjects.index') }}" class="block px-4 py-3 bg-gray-200 font-semibold text-gray-800">Kelola Data Mapel Dan Jam Pelajaran</a>
                            <a href="{{ route('reports.index') }}" class="block px-4 py-3 hover:bg-gray-100">Rekap Absensi</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-100">Logout</button>
                            </form>
                        </nav>
                    </aside>

                    <main class="flex-1 p-6">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">DASHBOARD ADMIN</h3>
                            <p class="text-sm text-gray-500">By SMK TEKNOLOGI KOTAWARINGIN</p>
                        </div>

                        @if (session('status'))
                            <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="bg-gray-200 p-4 rounded-lg">
                            <div class="bg-white p-4 rounded-lg border border-gray-300 space-y-4">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-lg font-semibold text-gray-800">Kelola Data Mapel</h4>
                                    <button type="button" onclick="document.getElementById('createSubjectForm').classList.toggle('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">
                                        + Tambah Mapel Dan Jam Mapel
                                    </button>
                                </div>

                                <form id="createSubjectForm" method="POST" action="{{ route('subjects.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-md border border-dashed border-gray-300 hidden">
                                    @csrf
                                    @php
                                        $oldTimeSlot = old('time_slot');
                                        $timeParts = $oldTimeSlot ? explode('-', $oldTimeSlot) : [];
                                        $oldStart = isset($timeParts[0]) ? trim($timeParts[0]) : '';
                                        $oldEnd = isset($timeParts[1]) ? trim($timeParts[1]) : '';
                                    @endphp
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Kode Mapel</label>
                                        <input name="code" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="Contoh: MAT101" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                                        <input name="name" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                                    </div>
                                    <div x-data>
                                        <label class="block text-sm font-medium text-gray-700">Jam Pelajaran</label>
                                        <div class="mt-1 flex flex-col gap-2 time-slot-picker md:flex-row md:items-center">
                                            <div class="w-full">
                                                <input id="timepicker-start" type="text" class="w-full rounded border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Mulai (07.00)" value="{{ $oldStart ? str_replace('.', ':', $oldStart) : '' }}" autocomplete="off">
                                            </div>
                                            <span class="text-gray-500 text-xs text-center md:w-auto">s/d</span>
                                            <div class="w-full">
                                                <input id="timepicker-end" type="text" class="w-full rounded border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Selesai (09.00)" value="{{ $oldEnd ? str_replace('.', ':', $oldEnd) : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <input type="hidden" name="time_slot" id="time-slot-hidden" value="{{ $oldTimeSlot }}">
                                        <p id="time-slot-preview" class="text-xs text-gray-500 mt-1">
                                            {{ $oldTimeSlot ?: 'Format otomatis: 07.00-09.00' }}
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                                        <select name="class_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                                            <option value="">Pilih kelas</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                        <input name="classroom" class="mt-2 w-full rounded border-gray-300 text-sm" placeholder="Atau ketik manual">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Guru</label>
                                        <select name="teacher_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                                            <option value="">-</option>
                                            @foreach ($teachers as $teacher)
                                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Siswa</label>
                                        <select name="students[]" multiple class="mt-1 w-full rounded border-gray-300 text-sm h-32">
                                            @foreach ($students as $student)
                                                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->classroom ?? '-' }})</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk pilih lebih dari satu.</p>
                                    </div>
                                    <div class="md:col-span-3">
                                        <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Simpan Data</button>
                                    </div>
                                </form>

                                <div class="bg-gray-50 p-4 rounded-md border border-dashed border-gray-300">
                                    <h5 class="text-sm font-semibold text-gray-700 mb-2">Tambah Kelas</h5>
                                    <form method="POST" action="{{ route('classes.store') }}" class="flex gap-3 items-center">
                                    @csrf
                                    <input name="name" class="flex-1 rounded border-gray-300 text-sm" placeholder="Nama kelas (mis. X IPA 1)" required>
                                    <button class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">Tambah</button>
                                    </form>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Mata Pelajaran</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Jam Pelajaran</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Guru</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Siswa</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse ($subjects as $subject)
                                                <tr>
                                                    <td class="px-4 py-2">
                                                        <div class="font-semibold text-gray-800">{{ $subject->name }}</div>
                                                        @if ($subject->classroom)
                                                            <div class="text-xs text-gray-500">Kelas: {{ $subject->classroom }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-700">{{ $subject->time_slot ?? '-' }}</td>
                                                    <td class="px-4 py-2 text-gray-700">{{ $subject->teacher->name ?? '-' }}</td>
                                                    <td class="px-4 py-2 text-gray-700">
                                                        @if ($subject->students->isEmpty())
                                                            -
                                                        @else
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach ($subject->students as $student)
                                                                    <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $student->name }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 space-x-2">
                                                        <a href="{{ route('subjects.edit', $subject) }}" class="text-blue-600 hover:text-blue-700">Edit</a>
                                                        <form method="POST" action="{{ route('subjects.destroy', $subject) }}" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-red-600 hover:text-red-700" onclick="return confirm('Hapus mapel ini?')">Hapus</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada data mapel.</td>
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

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/timepicker-ui@3.2.0/dist/css/main.css">
    <style>
        .time-slot-picker .timepicker-ui {
            display: block;
            width: 100%;
        }
        .time-slot-picker .timepicker-ui .timepicker-ui-input-wrapper,
        .time-slot-picker .timepicker-ui input {
            width: 100%;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/timepicker-ui@3.2.0/dist/index.umd.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const startInput = document.getElementById('timepicker-start');
            const endInput = document.getElementById('timepicker-end');
            const hiddenInput = document.getElementById('time-slot-hidden');
            const preview = document.getElementById('time-slot-preview');

            if (!startInput || !endInput || !hiddenInput) {
                return;
            }

            const formatSegment = (value) => {
                if (!value) return '';
                const cleaned = value.trim();
                if (!cleaned) return '';
                const parts = cleaned.split(/[:.]/).map(part => part.replace(/\D/g, '')).filter(Boolean);
                if (!parts.length) return '';
                const hours = parts[0].padStart(2, '0').slice(0, 2);
                const minutes = (parts[1] ?? '00').padEnd(2, '0').slice(0, 2);
                return `${hours}.${minutes}`;
            };

            const updateHiddenValue = () => {
                const startValue = formatSegment(startInput.value);
                const endValue = formatSegment(endInput.value);
                if (startValue && endValue) {
                    hiddenInput.value = `${startValue}-${endValue}`;
                    if (preview) {
                        preview.textContent = hiddenInput.value;
                    }
                } else {
                    hiddenInput.value = '';
                    if (preview) {
                        preview.textContent = 'Format otomatis: 07.00-09.00';
                    }
                }
            };

            const bindPicker = (input) => {
                if (!window.TimepickerUI || !input) return;
                const picker = new window.TimepickerUI(input, {
                    clockType: '24h',
                    enableSwitchIcon: true,
                    focusInputAfterCloseModal: false,
                    cancelLabel: 'Batal',
                    okLabel: 'Pilih',
                    timeLabel: 'Pilih Jam'
                });
                picker.create();
            };

            bindPicker(startInput);
            bindPicker(endInput);

            const attachListeners = (input) => {
                ['change', 'input', 'blur'].forEach(eventName => {
                    input.addEventListener(eventName, updateHiddenValue);
                });
                const host = input.closest('.timepicker-ui');
                (host || input).addEventListener('timepicker:confirm', updateHiddenValue);
            };

            attachListeners(startInput);
            attachListeners(endInput);

            updateHiddenValue();
        });
    </script>
@endpush
</x-app-layout>
