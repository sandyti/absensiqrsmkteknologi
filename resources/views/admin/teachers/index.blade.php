<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola Data Guru
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Data Guru</h3>
                    <button type="button" onclick="document.getElementById('createForm').classList.toggle('hidden')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">
                        + Tambah Guru
                    </button>
                </div>

                <form id="createForm" method="POST" action="{{ route('teachers.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-md border border-dashed border-gray-300 hidden">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama</label>
                        <input name="nama" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIP</label>
                        <input name="nip" class="mt-1 w-full rounded border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input name="username" type="text" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input name="password" type="text" class="mt-1 w-full rounded border-gray-300 text-sm" placeholder="password">
                    </div>
                    <div class="md:col-span-4">
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Simpan Data</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Nama</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">NIP</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Username</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($teachers as $teacher)
                                <tr>
                                    <td class="px-4 py-2">{{ $teacher->guruProfile?->nama ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $teacher->guruProfile?->nip ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $teacher->username }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('teachers.edit', $teacher) }}" class="text-blue-600 hover:text-blue-700">Edit</a>
                                            <form method="POST" action="{{ route('teachers.destroy', $teacher) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:text-red-700" onclick="return confirm('Hapus guru ini?')">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada data guru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
