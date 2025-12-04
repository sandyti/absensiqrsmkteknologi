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
                            <a href="{{ route('students.index') }}" class="block px-4 py-3 bg-gray-200 font-semibold text-gray-800">Kelola Data Siswa</a>
                            <a href="{{ route('subjects.index') }}" class="block px-4 py-3 hover:bg-gray-100">Kelola Data Mapel Dan Jam Pelajaran</a>
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
                                    <h4 class="text-lg font-semibold text-gray-800">Kelola Data Siswa</h4>
                                    <button type="button" onclick="document.getElementById('createStudentForm').classList.toggle('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">
                                        + Tambah Siswa
                                    </button>
                                </div>

                                <form id="createStudentForm" method="POST" action="{{ route('students.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-md border border-dashed border-gray-300 hidden">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nama Siswa</label>
                                        <input name="name" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input name="email" type="email" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Password (opsional, default: password)</label>
                                        <input name="password" type="text" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="password">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">NIS/NISN</label>
                                        <input name="identifier" class="mt-1 w-full rounded border-gray-300 text-sm">
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
                                    <div class="md:col-span-3">
                                        <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Simpan Data</button>
                                    </div>
                                </form>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Nama Siswa</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Kelas</th>
                                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse ($students as $student)
                                                <tr>
                                                    <td class="px-4 py-2">
                                                        <div class="font-semibold text-gray-800">{{ $student->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $student->email }}</div>
                                                        @if ($student->identifier)
                                                            <div class="text-xs text-gray-500">ID: {{ $student->identifier }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 text-gray-700">{{ $student->classroom ?? '-' }}</td>
                                                    <td class="px-4 py-2 space-x-2">
                                                        <a href="{{ route('students.edit', $student) }}" class="text-blue-600 hover:text-blue-700">Edit</a>
                                                        <form method="POST" action="{{ route('students.destroy', $student) }}" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="text-red-600 hover:text-red-700" onclick="return confirm('Hapus siswa ini?')">Hapus</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada data siswa.</td>
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
</x-app-layout>
