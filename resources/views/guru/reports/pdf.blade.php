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

        <h1>Laporan Presensi Guru</h1>
        <div class="subtitle">Rentang: {{ $titleRange }}</div>

        <div class="meta">
            <strong>Tanggal export:</strong> {{ now()->translatedFormat('d F Y H:i') }}<br>
            <strong>Total data:</strong> {{ $records->count() }}
        </div>

        @if($showGuruDetail)
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Status</th>
                        <th>Metode</th>
                        <th>Guru</th>
                        <th>Diubah Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->scanned_at?->translatedFormat('d F Y H:i') ?? '-' }}</td>
                            <td>{{ $record->siswa?->nama ?? '-' }}</td>
                            <td>{{ $record->siswa?->kelas?->nama ?? '-' }}</td>
                            <td>{{ $record->sesiPresensi?->jadwal?->mapel?->nama_mapel ?? '-' }}</td>
                            <td style="text-transform: capitalize;">{{ $record->status }}</td>
                            <td style="text-transform: capitalize;">{{ $record->method }}</td>
                            <td>{{ $record->sesiPresensi?->jadwal?->guru?->nama ?? '-' }}</td>
                            <td>{{ $record->editor?->nama ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data presensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <table style="margin-bottom: 12px;">
                <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Hadir</th>
                        <th>Sakit</th>
                        <th>Izin</th>
                        <th>Alpha</th>
                        <th>Terlambat</th>
                        <th>% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recapRows as $row)
                        <tr>
                            <td>{{ $row['nama'] }}</td>
                            <td>{{ $row['kelas'] }}</td>
                            <td>{{ $row['hadir'] }}</td>
                            <td>{{ $row['sakit'] }}</td>
                            <td>{{ $row['izin'] }}</td>
                            <td>{{ $row['alpa'] }}</td>
                            <td>{{ $row['terlambat'] }}</td>
                            <td>{{ number_format($row['persentase_kehadiran'], 2) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">Belum ada data rekap siswa.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <table style="margin-bottom: 12px;">
                <thead>
                    <tr>
                        <th>Mapel</th>
                        <th>Guru</th>
                        <th>Pertemuan</th>
                        <th>Hadir</th>
                        <th>Izin</th>
                        <th>Alpha</th>
                        <th>% Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recapBySubject as $row)
                        <tr>
                            <td>{{ $row['mapel'] }}</td>
                            <td>{{ $row['guru'] }}</td>
                            <td>{{ $row['pertemuan'] }}</td>
                            <td>{{ $row['hadir'] }}</td>
                            <td>{{ $row['izin'] }}</td>
                            <td>{{ $row['alpa'] }}</td>
                            <td>{{ number_format($row['persentase_kehadiran'], 2) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Belum ada data rekap mapel.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <table>
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Jumlah Siswa</th>
                        <th>Pertemuan</th>
                        <th>Kehadiran</th>
                        <th>Alpha</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recapByClass as $row)
                        <tr>
                            <td>{{ $row['kelas'] }}</td>
                            <td>{{ $row['jumlah_siswa'] }}</td>
                            <td>{{ $row['pertemuan'] }}</td>
                            <td>{{ $row['kehadiran'] }}</td>
                            <td>{{ $row['alpa'] }}</td>
                            <td>{{ number_format($row['persentase'], 2) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Belum ada data rekap kelas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
