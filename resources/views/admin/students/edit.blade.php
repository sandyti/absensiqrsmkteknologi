<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Data Siswa
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <form method="POST" action="{{ route('students.update', $student) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Siswa</label>
                        <input name="name" value="{{ old('name', $student->name) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input name="email" type="email" value="{{ old('email', $student->email) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password (biarkan kosong jika tidak diubah)</label>
                        <input name="password" type="text" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="password baru">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIS/NISN</label>
                        <input name="identifier" value="{{ old('identifier', $student->identifier) }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="class_id" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="">Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id') == $class->id || $student->classroom === $class->name)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <input name="classroom" value="{{ old('classroom', $student->classroom) }}" class="mt-2 w-full rounded border-gray-300 text-sm" placeholder="Atau ketik manual">
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('students.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Kembali</a>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
