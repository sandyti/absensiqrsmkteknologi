<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Data Mapel
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                @php
                    $existingTimeSlot = old('time_slot', $subject->time_slot);
                    $slotParts = $existingTimeSlot ? explode('-', $existingTimeSlot) : [];
                    $slotStart = isset($slotParts[0]) ? trim($slotParts[0]) : '';
                    $slotEnd = isset($slotParts[1]) ? trim($slotParts[1]) : '';
                @endphp
                <form method="POST" action="{{ route('subjects.update', $subject) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode Mapel</label>
                        <input name="code" value="{{ old('code', $subject->code) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                        <input name="name" value="{{ old('name', $subject->name) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Jam Pelajaran</label>
                        <div class="mt-1 flex flex-col gap-2 time-slot-picker md:flex-row md:items-center">
                            <div class="w-full">
                                <input id="edit-timepicker-start" type="text" class="w-full rounded border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Mulai (07.00)" value="{{ $slotStart ? str_replace('.', ':', $slotStart) : '' }}" autocomplete="off">
                            </div>
                            <span class="text-gray-500 text-xs text-center md:w-auto">s/d</span>
                            <div class="w-full">
                                <input id="edit-timepicker-end" type="text" class="w-full rounded border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Selesai (09.00)" value="{{ $slotEnd ? str_replace('.', ':', $slotEnd) : '' }}" autocomplete="off">
                            </div>
                        </div>
                        <input type="hidden" name="time_slot" id="edit-time-slot-hidden" value="{{ $existingTimeSlot }}">
                        <p id="edit-time-slot-preview" class="text-xs text-gray-500 mt-1">
                            {{ $existingTimeSlot ?: 'Format otomatis: 07.00-09.00' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="class_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="">Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id', $subject->class_id) == $class->id || $subject->classroom === $class->name)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <input name="classroom" value="{{ old('classroom', $subject->classroom) }}" class="mt-2 w-full rounded border-gray-300 text-sm" placeholder="Atau ketik manual">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Guru</label>
                        <select name="teacher_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="">-</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $subject->teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Siswa</label>
                        <select name="students[]" multiple class="mt-1 w-full rounded border-gray-300 text-sm h-40">
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected(in_array($student->id, old('students', $subject->students->pluck('id')->toArray())))>
                                    {{ $student->name }} ({{ $student->classroom ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk pilih lebih dari satu.</p>
                    </div>

                    <div class="md:col-span-2 flex items-center justify-between pt-2">
                        <a href="{{ route('subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Kembali</a>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Simpan Perubahan</button>
                    </div>
                </form>
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
            const startInput = document.getElementById('edit-timepicker-start');
            const endInput = document.getElementById('edit-timepicker-end');
            const hiddenInput = document.getElementById('edit-time-slot-hidden');
            const preview = document.getElementById('edit-time-slot-preview');

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
