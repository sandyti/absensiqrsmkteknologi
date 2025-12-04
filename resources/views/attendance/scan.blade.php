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
                <h3 class="text-2xl font-extrabold text-gray-900 tracking-wide">SCAN QR SISWA</h3>
            </div>

            @if (session('status'))
                <div class="rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="border border-gray-300 rounded-md p-4 space-y-4">
                <div class="w-full text-center font-semibold text-gray-800 border border-gray-200 rounded-md py-2 bg-gray-50">
                    SCAN QR BARCODE
                </div>
                <div class="border border-gray-300 rounded-md space-y-2">
                    <div id="qr-reader" class="w-full"></div>
                    <div class="flex gap-2">
                        <button id="toggle-camera" type="button" class="flex-1 border border-gray-400 rounded-md py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                            Kamera Depan/Belakang
                        </button>
                        <button id="toggle-flash" type="button" class="flex-1 border border-gray-400 rounded-md py-2 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                            Flashlight
                        </button>
                    </div>
                    <div class="text-sm text-gray-600" id="qr-status">Arahkan ke QR</div>
                </div>
                <form id="confirm-form" method="POST" action="{{ route('attendance.scan.confirm') }}" class="space-y-2">
                    @csrf
                    <input type="hidden" name="code" id="code-field">
                    <button id="confirm-btn" class="w-full border border-gray-400 rounded-md py-2 text-center font-semibold text-gray-800 hover:bg-gray-50" disabled>
                        Konfirmasi
                    </button>
                </form>
            </div>

            <div class="text-center text-xs text-gray-500 border-t border-gray-200 pt-3">
                By SMK TEKNOLOGI KOTAWARINGIN
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        const statusText = document.getElementById('qr-status');
        const codeField = document.getElementById('code-field');
        const confirmBtn = document.getElementById('confirm-btn');
        const confirmForm = document.getElementById('confirm-form');
        const toggleCamera = document.getElementById('toggle-camera');
        const toggleFlash = document.getElementById('toggle-flash');
        let facingMode = "environment";
        let html5QrCode;
        let flashOn = false;

        function startScanner() {
            if (html5QrCode) {
                html5QrCode.stop().catch(() => {});
            }
            html5QrCode = new Html5Qrcode("qr-reader");
            const config = typeof facingMode === 'string'
                ? { facingMode }
                : { facingMode: 'environment' };

            html5QrCode.start(
                config,
                {
                    fps: 10,
                    qrbox: 250,
                },
                qrCodeMessage => {
                    codeField.value = qrCodeMessage;
                    statusText.textContent = 'Kode ditemukan. Tekan konfirmasi.';
                    confirmBtn.disabled = false;
                    html5QrCode.stop().catch(() => {});
                },
                errorMessage => {
                    // ignore scan errors
                }
            ).catch(err => {
                statusText.textContent = 'Tidak bisa membuka kamera: ' + err;
            });
        }

        toggleCamera?.addEventListener('click', () => {
            facingMode = facingMode === "environment" ? "user" : "environment";
            startScanner();
        });

        toggleFlash?.addEventListener('click', async () => {
            if (!html5QrCode) {
                statusText.textContent = 'Mulai pemindaian dulu sebelum menyalakan flash.';
                return;
            }

            const canCheckState = typeof html5QrCode.getState === 'function';
            const isScanning = !canCheckState || (typeof Html5QrcodeScannerState !== 'undefined' && html5QrCode.getState() === Html5QrcodeScannerState.SCANNING);
            if (!isScanning) {
                statusText.textContent = 'Pemindaian belum berjalan. Tekan mulai ulang.';
                return;
            }

            try {
                const capabilities = await html5QrCode.getRunningTrackCapabilities();
                if (capabilities && capabilities.torch) {
                    flashOn = !flashOn;
                    await html5QrCode.applyVideoConstraints({ advanced: [{ torch: flashOn }] });
                    statusText.textContent = flashOn ? 'Flashlight menyala.' : 'Flashlight dimatikan.';
                } else {
                    statusText.textContent = 'Flash tidak didukung di perangkat ini.';
                }
            } catch (err) {
                statusText.textContent = 'Tidak bisa mengubah flashlight: ' + err;
            }
        });

        confirmForm?.addEventListener('submit', (event) => {
            if (!codeField.value) {
                event.preventDefault();
                statusText.textContent = 'Scan QR terlebih dahulu sebelum konfirmasi.';
                alert('Scan QR terlebih dahulu sebelum konfirmasi.');
                return;
            }
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Mengirim...';
            statusText.textContent = 'Mengirim konfirmasi...';
            alert('Konfirmasi dikirim. Jika berhasil, halaman akan diperbarui.');
        });

        startScanner();
    </script>
    @endpush
</x-app-layout>
