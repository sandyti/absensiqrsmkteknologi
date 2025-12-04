<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-700 text-xl">&larr;</a>
            <x-application-logo class="h-10 w-auto" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4">
            <div class="text-center">
                <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">RIWAYAT ABSENSI</h3>
            </div>

            <form method="GET" class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Periode</label>
                    <select name="range" class="mt-1 w-full rounded border-gray-300 text-sm">
                        <option value="hari" @selected($range === 'hari')>Per Hari</option>
                        <option value="minggu" @selected($range === 'minggu')>Per Minggu</option>
                        <option value="bulan" @selected($range === 'bulan')>Per Bulan</option>
                        <option value="tahun" @selected($range === 'tahun')>Per Tahun</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Acuan</label>
                    <input type="date" name="date" value="{{ $anchor->toDateString() }}" class="mt-1 w-full rounded border-gray-300 text-sm">
                </div>
                <div class="flex justify-end">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Terapkan</button>
                </div>
            </form>

            <div class="border border-gray-300 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Rentang</p>
                        <p class="text-lg font-semibold text-gray-800">{{ $titleRange }}</p>
                    </div>
                    <p class="text-sm text-gray-500">{{ $records->total() }} data</p>
                </div>
                <div class="space-y-3 max-h-80 overflow-y-auto text-sm text-gray-800">
                    @forelse ($records as $record)
                        <div class="border border-gray-200 rounded-md p-2">
                            <div class="text-xs text-gray-500">{{ $record->date->translatedFormat('d F Y') }}</div>
                            <div class="capitalize font-semibold">{{ $record->status }}</div>
                            @if ($record->note)
                                <div class="text-xs text-gray-600 mt-1">Catatan: {{ $record->note }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-gray-500">Belum ada data absensi.</p>
                    @endforelse
                </div>
                <div class="mt-3">
                    {{ $records->links() }}
                </div>
            </div>

            <div class="text-center text-xs text-gray-500 border-t border-gray-200 pt-3">
                By SMK TEKNOLOGI KOTAWARINGIN
            </div>
        </div>
    </div>
</x-app-layout>
