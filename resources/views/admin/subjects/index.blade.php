<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola Data Mapel
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
                            <a href="{{ route('subjects.index') }}" class="block px-4 py-3 bg-gray-200 font-semibold text-gray-800">Kelola Data Mapel</a>
                            <a href="{{ route('reports.index') }}" class="block px-4 py-3 hover:bg-gray-100">Rekap Absensi</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-100">Logout</button>
                            </form>
                        </nav>
                    </aside>

                    <main class="flex-1 p-6">
                        @if (session('status'))
                            <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800">Data Mapel</h3>
                                <button type="button" onclick="document.getElementById('createForm').classList.toggle('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">
                                    + Tambah Mapel
                                </button>
                            </div>

                            <form id="createForm" method="POST" action="{{ route('subjects.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end bg-gray-50 p-4 rounded-md border border-dashed border-gray-300 hidden">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Mapel</label>
                                    <input name="nama_mapel" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="Nama mapel" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kelas</label>
                                    <select name="id_kelas" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                                        <option value="">Pilih kelas</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id_kelas }}">{{ $class->nama }} - {{ $class->tingkat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jam Pelajaran</label>
                                    <input name="jam_pelajaran" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="Contoh: 07:00 - 08:30">
                                </div>
                                <div class="md:col-span-3">
                                    <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Simpan</button>
                                </div>
                            </form>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Nama Mapel</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Kelas</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Jam Pelajaran</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-700">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse ($subjects as $subject)
                                            <tr>
                                                <td class="px-4 py-2">{{ $subject->nama_mapel }}</td>
                                                <td class="px-4 py-2">
                                                    @if ($subject->kelas->isNotEmpty())
                                                        {{ $subject->kelas->map(fn ($class) => $class->nama.' - '.$class->tingkat)->implode(', ') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2">{{ $subject->jam_pelajaran ?? '-' }}</td>
                                                <td class="px-4 py-2">
                                                    <div class="flex items-center gap-3">
                                                        <a href="{{ route('subjects.edit', $subject) }}" class="text-blue-600 hover:text-blue-700">Edit</a>
                                                        <form method="POST" action="{{ route('subjects.destroy', $subject) }}" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-red-600 hover:text-red-700" onclick="return confirm('Hapus mapel ini?')">Hapus</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada data mapel.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
