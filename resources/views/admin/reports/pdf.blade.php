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
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; }
        th { background: #f3f4f6; font-size: 10px; text-align: left; }
        td { font-size: 10px; }
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

        <table>
            <thead>
                <tr>
                    <th style="width: 3%;" class="text-center">No</th>
                    <th style="width: 8%;">NIS</th>
                    <th style="width: 15%;">Nama</th>
                    <th style="width: 9%;">Kelas</th>
                    <th style="width: 5%;" class="text-center">Hadir</th>
                    <th style="width: 5%;" class="text-center">Sakit</th>
                    <th style="width: 5%;" class="text-center">Izin</th>
                    <th style="width: 5%;" class="text-center">Alpha</th>
                    <th style="width: 6%;" class="text-center">Terlambat</th>
                    <th style="width: 8%;" class="text-center">Total Pertemuan</th>
                    <th style="width: 9%;" class="text-center">Persentase Kehadiran</th>
                    <th style="width: 12%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recapRows as $row)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $row['nis'] }}</td>
                        <td>{{ $row['nama'] }}</td>
                        <td>{{ $row['kelas'] }}</td>
                        <td class="text-center">{{ $row['hadir'] }}</td>
                        <td class="text-center">{{ $row['sakit'] }}</td>
                        <td class="text-center">{{ $row['izin'] }}</td>
                        <td class="text-center">{{ $row['alpa'] }}</td>
                        <td class="text-center">{{ $row['terlambat'] }}</td>
                        <td class="text-center">{{ $row['total_pertemuan'] }}</td>
                        <td class="text-center">{{ number_format($row['persentase_kehadiran'], 2) }}%</td>
                        <td>{{ $row['keterangan'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">Belum ada data presensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
