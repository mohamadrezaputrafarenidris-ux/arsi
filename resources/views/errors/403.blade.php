<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak — Sistem Pengajuan dan Persetujuan KRS</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
<div class="login-bungkus">
    <div class="login-kartu">
        <div class="merek-besar">Sistem KRS</div>
        <div class="sub">Pengajuan dan Persetujuan Kartu Rencana Studi</div>

        <h1 style="font-size:1.2rem;">403 &mdash; Akses Ditolak</h1>
        <p class="teks-kecil">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman tersebut.' }}
        </p>

        @auth
            <a class="tombol tombol-utama" href="{{ route(auth()->user()->role->homeRoute()) }}">Kembali ke Beranda</a>
        @else
            <a class="tombol tombol-utama" href="{{ route('login') }}">Kembali ke Halaman Masuk</a>
        @endauth
    </div>
</div>
</body>
</html>
