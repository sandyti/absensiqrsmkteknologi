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
                        <input name="nama" value="{{ old('nama', $student->nama) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input name="username" type="text" value="{{ old('username', $student->username) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password (biarkan kosong jika tidak diubah)</label>
                        <input name="password" type="text" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="password baru">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIS</label>
                        <input name="nis" value="{{ old('nis', $student->nis) }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="id_kelas" class="mt-1 w-full rounded border-gray-300 text-sm">
                            <option value="">Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id_kelas }}" @selected(old('id_kelas') == $class->id_kelas || $student->siswaProfile?->id_kelas == $class->id_kelas)>{{ $class->nama }} - {{ $class->tingkat }}</option>
                            @endforeach
                        </select>
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
