<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 0; }
        .page { padding: 20px 24px 28px; }
        .header img { width: 100%; max-height: 140px; object-fit: contain; }
        h1 { font-size: 18px; margin: 12px 0 4px; text-align: center; letter-spacing: 0.5px; }
        .subtitle { text-align: center; color: #6b7280; font-size: 11px; margin-bottom: 16px; }
        .meta { margin-bottom: 14px; font-size: 11px; color: #374151; }
        .meta strong { color: #111827; }
        .summary { margin-bottom: 14px; }
        .summary td { font-size: 11px; padding: 5px 8px; }
        .summary .label { font-weight: 700; width: 22%; background: #f9fafb; }
        .summary .value { width: 28%; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; }
        th { background: #f3f4f6; font-size: 11px; text-align: left; }
        td { font-size: 11px; }
        .text-center { text-align: center; }
        .small { font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="page">
        @if($kopData)
            <div class="header">
                <img src="{{ $kopData }}" alt="Kop Surat">
            </div>
        @endif

        <h1>Rekap Presensi</h1>
        <div class="subtitle">Rentang: {{ $titleRange }}</div>

        <div class="meta">
            <strong>Tanggal export:</strong> {{ now()->translatedFormat('d F Y H:i') }}<br>
            <strong>Total data:</strong> {{ $records->count() }}
        </div>

        <table class="summary">
            <tbody>
                <tr>
                    <td class="label">Total Hadir</td>
                    <td class="value">{{ $statusTotals['hadir'] ?? 0 }}</td>
                    <td class="label">Total Sakit</td>
                    <td class="value">{{ $statusTotals['sakit'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="label">Total Izin</td>
                    <td class="value">{{ $statusTotals['izin'] ?? 0 }}</td>
                    <td class="label">Total Terlambat</td>
                    <td class="value">{{ $statusTotals['terlambat'] ?? 0 }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 18%;">Waktu</th>
                    <th style="width: 25%;">Siswa</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 15%;">Metode</th>
                    <th style="width: 20%;">Diubah Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ $record->scanned_at?->translatedFormat('d F Y H:i') ?? '-' }}</td>
                        <td>
                            <div><strong>{{ $record->siswa?->nama ?? '-' }}</strong></div>
                            <div class="small">{{ $record->siswa?->kelas?->nama ?? '-' }}</div>
                        </td>
                        <td style="text-transform: capitalize;">{{ $record->status }}</td>
                        <td style="text-transform: capitalize;">{{ $record->method }}</td>
                        <td>{{ $record->editor?->nama ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data presensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
