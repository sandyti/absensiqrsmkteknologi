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
                    <label class="block text-sm font-medium text-gray-700">Pilih Kelas</label>
                    <select name="class_id" class="mt-1 w-full rounded border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="">Pilih Kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected($selectedClassId == $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pilih Mapel</label>
                    <select name="subject_id" class="mt-1 w-full rounded border-gray-300 text-sm" onchange="this.form.submit()">
                        <option value="">Pilih Mapel</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($selectedSubjectId == $subject->id)>{{ $subject->code ?? '' }} {{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pilih Jam Pelajaran</label>
                    <input class="mt-1 w-full rounded border-gray-300 text-sm" value="{{ $selectedSubject?->time_slot }}" readonly placeholder="Otomatis dari mapel">
                </div>
                <div>
                    <p class="text-xs text-gray-500">Daftar siswa diambil otomatis berdasarkan kelas yang dipilih.</p>
                </div>
        </form>

            <div class="border border-gray-300 rounded-md p-4 text-center">
                @if ($showQr && $activeSession)
                    <div id="qrcode" class="flex justify-center"></div>
                    <p class="mt-2 text-sm text-gray-600" id="code-label">Kode: {{ $activeSession->code }}</p>
                    <p class="text-xs text-gray-500">Status: {{ ucfirst($activeSession->status) }}</p>
                @else
                    <p class="text-sm text-gray-600">Pilih kelas dan mapel kemudian tekan Generate untuk memulai sesi.</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-2">
                <form method="POST" action="{{ route('attendance.session.start') }}">
                    @csrf
                    <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                    <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                    <button class="w-full border border-gray-400 rounded-md py-2 text-center font-semibold text-gray-800 hover:bg-gray-50" {{ !$selectedClassId || !$selectedSubjectId ? 'disabled' : '' }}>
                        Generate
                    </button>
                </form>
                @if ($showQr && $activeSession)
                    @if ($activeSession->status === 'active')
                        <form method="POST" action="{{ route('attendance.session.pause') }}">
                            @csrf
                            <button class="w-full border border-gray-400 rounded-md py-2 text-center font-semibold text-gray-800 hover:bg-gray-50">Pause</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('attendance.session.resume') }}">
                            @csrf
                            <button class="w-full border border-gray-400 rounded-md py-2 text-center font-semibold text-gray-800 hover:bg-gray-50">Lanjutkan</button>
                        </form>
                    @endif
                @elseif ($activeSession)
                    <button class="w-full border border-gray-200 rounded-md py-2 text-center font-semibold text-gray-400" disabled>Pause</button>
                @endif
            </div>

            <form method="POST" action="{{ route('attendance.session.close') }}">
                @csrf
                <button class="w-full border border-gray-400 rounded-md py-2 text-center font-semibold text-gray-800 hover:bg-gray-50">
                    TUTUP SESI
                </button>
            </form>

            @if ($selectedClassId)
                <div class="border border-gray-200 rounded-md p-3 space-y-2">
                    <h4 class="font-semibold text-gray-800 text-sm">Input Manual (terlambat/izin/sakit)</h4>
                    <form method="POST" action="{{ route('attendance.session.manual') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                        <div>
                            <label class="block text-sm text-gray-700">Pilih Siswa</label>
                            <select name="student_id" id="student-select" class="mt-1 w-full rounded border-gray-300 text-sm" required>
                                <option value="">Pilih siswa</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} — {{ $student->classroom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="status" class="rounded border-gray-300 text-sm" required>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="hadir">Hadir</option>
                                <option value="alpa">Alpa</option>
                            </select>
                            <input name="note" class="rounded border-gray-300 text-sm" placeholder="Catatan (opsional)">
                        </div>
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
                            <span>{{ $scan->student->name ?? '-' }}</span>
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
            const initialCode = @json($activeSession->code ?? null);
            const refreshUrl = "{{ route('attendance.session.refresh') }}";
            const scanUrl = "{{ route('attendance.session.scans', ['class_id' => $selectedClassId, 'subject_id' => $selectedSubjectId]) }}";
            const csrf = "{{ csrf_token() }}";
            let qr;

            function renderQRCode(code) {
                if (!qr) {
                    qr = new QRCode(document.getElementById('qrcode'), {
                        width: 200,
                        height: 200,
                    });
                }
                qr.clear();
                qr.makeCode(code);
                document.getElementById('code-label').textContent = 'Kode: ' + code;
            }

            async function refreshCode() {
                try {
                    const response = await fetch(refreshUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });
                    if (response.ok) {
                        const data = await response.json();
                        renderQRCode(data.code);
                    }
                } catch (error) {
                    console.error('Gagal memperbarui kode QR', error);
                }
            }

            if (initialCode) {
                renderQRCode(initialCode);
                setInterval(refreshCode, 15000);
            }
            
            // Periodically fetch latest scan list without refreshing QR or the whole page.
            const scanIntervalMs = 5000;
            const scanListEl = document.getElementById('scan-list');

            function renderScans(items) {
                if (!scanListEl) return;
                if (!items.length) {
                    scanListEl.innerHTML = '<p class="text-xs text-gray-500">Belum ada scan.</p>';
                    return;
                }

                const rows = items.map(item => {
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

                scanListEl.innerHTML = rows;
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

            // initial load
            refreshScans();
        </script>
    @endpush
@endif
</x-app-layout>
