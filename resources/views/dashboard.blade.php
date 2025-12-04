<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm text-gray-500">Halo, {{ $user->name }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $user->isAdmin() ? 'Dashboard Admin' : 'Ringkasan Absensi' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($user->isAdmin())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
                    <div class="flex">
                        <aside class="w-60 bg-gray-50 border-r border-gray-200">
                            <div class="p-4 border-b border-gray-200 text-center">
                                <div class="border rounded-lg p-3 flex justify-center">
                                    <x-application-logo class="h-12 w-auto" />
                                </div>
                            </div>
                            <nav class="text-sm">
                                <a href="{{ route('dashboard') }}" class="block px-4 py-3 {{ request()->routeIs('dashboard') ? 'bg-gray-200 font-semibold text-gray-800' : 'hover:bg-gray-100' }}">Home</a>
                                <a href="{{ route('teachers.index') }}" class="block px-4 py-3 {{ request()->routeIs('teachers.*') ? 'bg-gray-200 font-semibold text-gray-800' : 'hover:bg-gray-100' }}">Kelola Data Guru</a>
                                <a href="{{ route('students.index') }}" class="block px-4 py-3 {{ request()->routeIs('students.*') ? 'bg-gray-200 font-semibold text-gray-800' : 'hover:bg-gray-100' }}">Kelola Data Siswa</a>
                                <a href="{{ route('subjects.index') }}" class="block px-4 py-3 {{ request()->routeIs('subjects.*') ? 'bg-gray-200 font-semibold text-gray-800' : 'hover:bg-gray-100' }}">Kelola Data Mapel Dan Jam Pelajaran</a>
                                <a href="{{ route('reports.index') }}" class="block px-4 py-3 {{ request()->routeIs('reports.*') ? 'bg-gray-200 font-semibold text-gray-800' : 'hover:bg-gray-100' }}">Rekap Absensi</a>
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

                            <div class="bg-gray-200 p-4 rounded-lg">
                                <div class="bg-white p-4 rounded-lg border border-gray-300">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div class="border border-gray-300 rounded-md p-3 text-center">
                                            <p class="font-semibold text-gray-700">Data Guru</p>
                                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $summary['total_guru'] }}</p>
                                        </div>
                                        <div class="border border-gray-300 rounded-md p-3 text-center">
                                            <p class="font-semibold text-gray-700">Data Siswa</p>
                                            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $summary['total_siswa'] }}</p>
                                        </div>
                                        <div class="border border-gray-300 rounded-md p-3 text-center">
                                            <p class="font-semibold text-gray-700">Data Mapel/Jam Mapel</p>
                                            <p class="text-2xl font-bold text-gray-900 mt-2">-</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Rentang</p>
                                            <p class="text-lg font-semibold">
                                                @if ($period === 'hari')
                                                    Hari ini ({{ $chartData['range_start'] }})
                                                @elseif ($period === 'minggu')
                                                    Minggu {{ $anchorDate->weekOfYear }} ({{ $chartData['range_start'] }} - {{ $chartData['range_end'] }})
                                                @elseif ($period === 'bulan')
                                                    {{ $anchorDate->translatedFormat('F Y') }}
                                                @else
                                                    Tahun {{ $anchorDate->year }}
                                                @endif
                                            </p>
                                        </div>
                                        <form method="GET" class="flex gap-2 items-center">
                                            <select name="period" class="rounded border-gray-300 text-sm">
                                                <option value="hari" @selected($period === 'hari')>Per Hari</option>
                                                <option value="minggu" @selected($period === 'minggu')>Per Minggu</option>
                                                <option value="bulan" @selected($period === 'bulan')>Per Bulan</option>
                                                <option value="tahun" @selected($period === 'tahun')>Per Tahun</option>
                                            </select>
                                            <input type="date" name="date" value="{{ $anchorDate->toDateString() }}" class="rounded border-gray-300 text-sm">
                                            <button class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Terapkan</button>
                                        </form>
                                    </div>

                                    <div>
                                        <canvas id="attendanceChart" height="120"></canvas>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
            @elseif ($user->isGuru())
                <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ url()->previous() }}" class="text-gray-700 text-2xl">&larr;</a>
                            <x-application-logo class="h-10 w-auto" />
                        </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">DASHBOARD GURU</h3>
                    </div>
                    <div class="space-y-3">
                        <a href="{{ route('attendance.session') }}" class="block w-full border border-gray-400 rounded-md py-3 text-center font-semibold text-gray-800 hover:bg-gray-50">
                            SESI ABSENSI
                        </a>
                        <a href="{{ route('reports.index') }}" class="block w-full border border-gray-400 rounded-md py-3 text-center font-semibold text-gray-800 hover:bg-gray-50">
                            REKAP ABSENSI
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full border border-gray-400 rounded-md py-3 text-center font-semibold text-gray-800 hover:bg-gray-50">
                                LOGOUT
                            </button>
                        </form>
                    </div>
                    <div class="text-center text-xs text-gray-500 border-t border-gray-200 pt-3">
                        By SMK TEKNOLOGI KOTAWARINGIN
                    </div>
                </div>
            @else
                <div class="max-w-md mx-auto bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ url()->previous() }}" class="text-gray-700 text-2xl">&larr;</a>
                            <x-application-logo class="h-10 w-auto" />
                        </div>
                    <div class="text-center">
                        <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">DASHBOARD SISWA</h3>
                    </div>
                    <div class="space-y-3">
                        <a href="{{ route('attendance.scan') }}" class="block w-full border border-gray-400 rounded-md py-3 text-center font-semibold text-gray-800 hover:bg-gray-50">
                            SCAN QR BARCODE
                        </a>
                        <a href="{{ route('attendance.me') }}" class="block w-full border border-gray-400 rounded-md py-3 text-center font-semibold text-gray-800 hover:bg-gray-50">
                            LIHAT RIWAYAT ABSENSI
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full border border-gray-400 rounded-md py-3 text-center font-semibold text-gray-800 hover:bg-gray-50">
                                LOGOUT
                            </button>
                        </form>
                    </div>
                    <div class="text-center text-xs text-gray-500 border-t border-gray-200 pt-3">
                        By SMK TEKNOLOGI KOTAWARINGIN
                    </div>
                </div>
@endif
        </div>
    </div>

    @if ($user->isAdmin())
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('attendanceChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['labels'] ?? []),
                        datasets: [{
                            label: 'Jumlah Absensi',
                            data: @json($chartData['data'] ?? []),
                            backgroundColor: ['#16a34a', '#f59e0b', '#0ea5e9', '#ef4444', '#8b5cf6'],
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: 'Grafik Kehadiran'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision:0 }
                            }
                        }
                    }
                });
            }
        </script>
        @endpush
    @endif
</x-app-layout>
