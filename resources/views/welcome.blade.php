<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Sistem Absensi') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700;space-grotesk:500,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --bg-gradient:
                radial-gradient(circle at 20% 20%, rgba(96,165,250,0.28), transparent 28%),
                radial-gradient(circle at 78% 18%, rgba(94,234,212,0.22), transparent 30%),
                radial-gradient(circle at 80% 78%, rgba(79,70,229,0.18), transparent 26%),
                linear-gradient(135deg, #f6f7fb, #eef2ff);
            --card-border: rgba(15, 23, 42, 0.06);
        }
        body {
            font-family: 'Manrope', system-ui, -apple-system, sans-serif;
        }
        .role-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 0.95rem 1.25rem;
            border-radius: 0.95rem;
            font-weight: 700;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.15);
            transition: transform 150ms ease, box-shadow 150ms ease;
            color: #fff;
            text-decoration: none;
        }
        .role-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.18);
        }
        .role-btn.admin { background: #0f172a; }
        .role-btn.admin:hover { background: #111827; }
        .role-btn.teacher { background: #3b82f6; }
        .role-btn.teacher:hover { background: #2563eb; }
        .role-btn.student { background: #10b981; }
        .role-btn.student:hover { background: #0ea371; }
    </style>
</head>
<body class="min-h-screen text-slate-900 bg-[var(--bg-gradient)]">
    <div class="min-h-screen flex flex-col overflow-hidden">
        <div class="relative z-10 flex-1 flex flex-col px-4 md:px-10">
            <header class="pt-8 pb-6 flex justify-center">
                <div class="flex items-center gap-3 bg-white px-5 py-4 rounded-2xl shadow-lg border border-[var(--card-border)]">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-12 w-auto md:h-14 drop-shadow" />
                    <div>
                        <p class="font-extrabold text-lg md:text-xl text-slate-900">Sistem Absensi Siswa Teknologi</p>
                        <p class="text-sm text-slate-500">SMK Teknologi Kotawaringin</p>
                    </div>
                </div>
            </header>

            <main class="flex-1 flex flex-col items-center text-center gap-8 pb-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 md:gap-6 w-full max-w-6xl items-stretch">
                    <div class="bg-white/95 rounded-2xl border border-[var(--card-border)] shadow-xl p-6 text-left flex flex-col gap-4">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Admin</h3>
                            <p class="text-sm text-slate-600 mt-1">Kelola data siswa, guru, kelas, jadwal, dan laporan absensi.</p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('login', ['role' => 'admin']) }}" class="role-btn admin">
                                Login Sebagai Admin
                            </a>
                        </div>
                    </div>

                    <div class="bg-white/95 rounded-2xl border border-[var(--card-border)] shadow-xl p-6 text-left flex flex-col gap-4">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Guru</h3>
                            <p class="text-sm text-slate-600 mt-1">Input absensi, lihat rekap kelas, dan pantau kehadiran siswa.</p>
                        </div>
                        <div class="mt-auto">
                            @if (Route::has('login'))
                                <a href="{{ route('login', ['role' => 'guru']) }}" class="role-btn teacher">
                                    Login Sebagai Guru
                                </a>
                            @else
                                <span class="text-sm text-slate-500">Route login belum tersedia.</span>
                            @endif

                        </div>
                    </div>

                    <div class="bg-white/95 rounded-2xl border border-[var(--card-border)] shadow-xl p-6 text-left flex flex-col gap-4">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-900">Siswa</h3>
                            <p class="text-sm text-slate-600 mt-1">Lihat riwayat absensi, status hadir/izin, dan keterlambatan.</p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('login', ['role' => 'siswa']) }}" class="role-btn student">
                                Login Sebagai Siswa
                            </a>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-6 text-center text-xs text-slate-500 border-t border-slate-200 bg-white/70">
                © 2025 • By SMK Teknologi Kotawaringin
            </footer>
        </div>
    </div>
</body>
</html>
