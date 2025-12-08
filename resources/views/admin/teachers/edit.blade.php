<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Data Guru
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <form method="POST" action="{{ route('teachers.update', $teacher) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Guru</label>
                        <input name="name" value="{{ old('name', $teacher->name) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input name="email" type="email" value="{{ old('email', $teacher->email) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password (biarkan kosong jika tidak diubah)</label>
                        <input name="password" type="text" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="password baru">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIP / Identifier</label>
                        <input name="identifier" value="{{ old('identifier', $teacher->identifier) }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas Yang Diajar</label>
                        @php
                            $assignedClasses = collect(explode(',', $teacher->teaches_class ?? ''))->map(fn($v) => trim($v))->filter()->toArray();
                        @endphp
                        <div class="mt-1 grid grid-cols-2 gap-2 text-sm max-h-32 overflow-y-auto border rounded p-2">
                            @foreach ($classes as $class)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}" class="rounded border-gray-300"
                                        @checked(in_array($class->name, $assignedClasses) || in_array($class->id, (array) old('class_ids', [])))>
                                    <span>{{ $class->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <input name="teaches_class" value="{{ old('teaches_class', $teacher->teaches_class) }}" class="mt-2 w-full rounded border-gray-300 text-sm" placeholder="Atau ketik manual (pisahkan dengan koma)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                        @php
                            $assignedSubjects = collect(explode(',', $teacher->subject ?? ''))->map(fn($v) => trim($v))->filter()->toArray();
                        @endphp
                        <div class="mt-1 grid grid-cols-2 gap-2 text-sm max-h-32 overflow-y-auto border rounded p-2">
                            @foreach ($subjects as $subject)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" class="rounded border-gray-300"
                                        @checked(in_array($subject->name, $assignedSubjects) || in_array($subject->id, (array) old('subject_ids', [])))>
                                    <span>{{ $subject->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <input name="subject" value="{{ old('subject', $teacher->subject) }}" class="mt-2 w-full rounded border-gray-300 text-sm" placeholder="Atau ketik manual (pisahkan dengan koma)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jam Pelajaran</label>
                        <input name="teaching_hours" value="{{ old('teaching_hours', $teacher->teaching_hours) }}" class="mt-1 w-full rounded border-gray-300 text-sm" readonly>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('teachers.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Kembali</a>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
