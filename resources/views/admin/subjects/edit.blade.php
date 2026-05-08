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
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <form method="POST" action="{{ route('subjects.update', $subject) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Mapel</label>
                        <input name="nama_mapel" value="{{ old('nama_mapel', $subject->nama_mapel) }}" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <div class="mt-1 max-h-40 overflow-y-auto rounded border border-gray-300 bg-white p-2 space-y-2">
                            @foreach ($classes as $class)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input
                                        type="checkbox"
                                        name="id_kelas[]"
                                        value="{{ $class->id_kelas }}"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        @checked(collect(old('id_kelas', $subject->kelas->pluck('id_kelas')->all()))->contains($class->id_kelas))
                                    >
                                    <span>{{ $class->nama }} - {{ $class->tingkat }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jam Pelajaran</label>
                        <input name="jam_pelajaran" value="{{ old('jam_pelajaran', $subject->jam_pelajaran) }}" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="Contoh: 07:00 - 08:30">
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Kembali</a>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
