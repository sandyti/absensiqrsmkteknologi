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
                <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">ABSENSI</h3>
            </div>

            @if (session('status'))
                <div class="rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pilih Jadwal</label>
                    <select name="jadwal_id" class="mt-1 w-full rounded border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="">Pilih Jadwal</option>
                        @foreach ($jadwals as $jadwal)
                            <option value="{{ $jadwal->id_jadwal }}" @selected($selectedJadwalId == $jadwal->id_jadwal)>
                                {{ $jadwal->kelas?->nama ?? '-' }} | {{ $jadwal->mapel?->nama_mapel ?? '-' }} | {{ $jadwal->guru?->nama ?? '-' }} | {{ $jadwal->hari }} {{ $jadwal->jam_mulai }}-{{ $jadwal->jam_selesai }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($selectedJadwal)
                    <div class="rounded border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 space-y-1">
                        <div><span class="font-semibold">Kelas:</span> {{ $selectedJadwal->kelas?->nama ?? '-' }} {{ $selectedJadwal->kelas?->tingkat ? '('.$selectedJadwal->kelas->tingkat.')' : '' }}</div>
                        <div><span class="font-semibold">Mapel:</span> {{ $selectedJadwal->mapel?->nama_mapel ?? '-' }}</div>
                        <div><span class="font-semibold">Guru:</span> {{ $selectedJadwal->guru?->nama ?? '-' }}</div>
                        <div><span class="font-semibold">Hari:</span> {{ $selectedJadwal->hari }}</div>
                        <div><span class="font-semibold">Jam:</span> {{ $selectedJadwal->jam_mulai }} - {{ $selectedJadwal->jam_selesai }}</div>
                    </div>
                @endif
            </form>

            <div class="border border-gray-300 rounded-md p-4 text-center">
                @if ($showQr && $activeSession)
                    <div id="qrcode" class="flex justify-center"></div>
                    <p class="mt-2 text-sm text-gray-600" id="token-label">Token: {{ $activeSession->token }}</p>
                    <p class="text-xs text-gray-500">Status: {{ ucfirst($activeSession->status) }}</p>
                @else
                    <p class="text-sm text-gray-600">Pilih jadwal lalu tekan Generate untuk membuat sesi presensi.</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2">
                <form method="POST" action="{{ route('attendance.session.start') }}">
                    @csrf
                    <input type="hidden" name="jadwal_id" value="{{ $selectedJadwalId }}">
                    <button class="w-full border border-gray-400 rounded-md py-2 text-center font-semibold text-gray-800 hover:bg-gray-50" {{ !$selectedJadwalId ? 'disabled' : '' }}>
                        Generate
                    </button>
                </form>
                <form method="POST" action="{{ route('attendance.session.close') }}">
                    @csrf
                    <input type="hidden" name="jadwal_id" value="{{ $selectedJadwalId }}">
                    <button class="w-full border border-gray-400 rounded-md py-2 text-center font-semibold text-gray-800 hover:bg-gray-50" {{ !$selectedJadwalId ? 'disabled' : '' }}>
                        Tutup
                    </button>
                </form>
            </div>

            @if ($selectedJadwal && $activeSession)
                <div class="border border-gray-200 rounded-md p-3 space-y-2">
                    <h4 class="font-semibold text-gray-800 text-sm">Input Manual (terlambat/izin/sakit)</h4>
                    <form method="POST" action="{{ route('attendance.session.manual') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="jadwal_id" value="{{ $selectedJadwalId }}">
                        <div>
                            <label class="block text-sm text-gray-700">Pilih Siswa</label>
                            <select name="id_siswa" id="student-select" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                                <option value="">Pilih siswa</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id_siswa }}">{{ $student->nama }} - {{ $student->kelas?->nama ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <select name="status" class="w-full rounded border-gray-300 text-sm" required>
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpa">Alpa</option>
                        </select>
                        <button class="w-full border border-gray-400 rounded-md py-2 text-center font-semibold text-gray-800 hover:bg-gray-50">
                            Simpan Manual
                        </button>
                    </form>
                </div>
            @endif

            <div class="border border-gray-200 rounded-md p-3">
                <h4 class="font-semibold text-gray-800 text-sm mb-2">Siswa Berhasil Scan</h4>
                <div id="scan-list" class="space-y-1 text-sm text-gray-700 max-h-40 overflow-y-auto">
                    @forelse ($scans as $scan)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-1">
                            <span>{{ $scan->siswa?->nama ?? $scan->student ?? '-' }}</span>
                            <span class="text-xs text-gray-500">{{ $scan->status }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">Belum ada scan.</p>
                    @endforelse
                </div>
            </div>

            <div class="text-center text-xs text-gray-500 border-t border-gray-200 pt-3">
                By SMK TEKNOLOGI KOTAWARINGIN
            </div>
        </div>
    </div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 10px;
            font-size: 0.875rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 6px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = $('#student-select');
            if (el.length) {
                el.select2({
                    placeholder: 'Pilih siswa',
                    allowClear: true,
                    width: '100%',
                });
            }
        });
    </script>
@endpush

@if ($showQr && $activeSession)
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            const initialToken = @json($activeSession->token ?? null);
            const refreshUrl = "{{ route('attendance.session.refresh') }}";
            const scanUrl = "{{ route('attendance.session.scans', ['jadwal_id' => $selectedJadwalId]) }}";
            const csrf = "{{ csrf_token() }}";
            let qr;

            function renderQRCode(token) {
                if (!qr) {
                    qr = new QRCode(document.getElementById('qrcode'), {
                        width: 200,
                        height: 200,
                    });
                }
                qr.clear();
                qr.makeCode(token);
                document.getElementById('token-label').textContent = 'Token: ' + token;
            }

            async function refreshToken() {
                try {
                    const response = await fetch(refreshUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ jadwal_id: {{ (int) $selectedJadwalId }} }),
                    });
                    if (response.ok) {
                        const data = await response.json();
                        renderQRCode(data.token);
                    }
                } catch (error) {
                    console.error('Gagal memperbarui token QR', error);
                }
            }

            if (initialToken) {
                renderQRCode(initialToken);
                setInterval(refreshToken, 15000);
            }

            const scanIntervalMs = 5000;
            const scanListEl = document.getElementById('scan-list');

            function renderScans(items) {
                if (!scanListEl) return;
                if (!items.length) {
                    scanListEl.innerHTML = '<p class="text-xs text-gray-500">Belum ada scan.</p>';
                    return;
                }

                scanListEl.innerHTML = items.map(item => {
                    const status = item.status ?? '-';
                    const time = item.time ? `<span class="text-[10px] text-gray-400">${item.time}</span>` : '';
                    return `
                        <div class="flex items-center justify-between border-b border-gray-100 pb-1">
                            <div class="flex flex-col">
                                <span>${item.student ?? '-'}</span>
                                ${time}
                            </div>
                            <span class="text-xs text-gray-500">${status}</span>
                        </div>
                    `;
                }).join('');
            }

            async function refreshScans() {
                try {
                    const response = await fetch(scanUrl, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    renderScans(data.data || []);
                } catch (error) {
                    console.error('Gagal memuat data scan', error);
                }
            }

            setInterval(() => {
                if (!document.hidden) {
                    refreshScans();
                }
            }, scanIntervalMs);

            refreshScans();
        </script>
    @endpush
@endif
</x-app-layout>
