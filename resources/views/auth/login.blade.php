<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk &mdash; Sistem Pengajuan dan Persetujuan KRS</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
<div class="login-bungkus">
    <div class="login-kartu">
        <div class="merek-besar">Sistem KRS</div>
        <div class="sub">Pengajuan dan Persetujuan Kartu Rencana Studi</div>

        @if (session('status'))
            <div class="pesan pesan-sukses">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="pesan pesan-gagal">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="form-grup">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       autocomplete="username" autofocus required>
                @error('username')
                    <div class="error-teks">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-grup">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
                @error('password')
                    <div class="error-teks">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="tombol tombol-utama">Masuk</button>
        </form>

        <p class="teks-kecil teks-redup" style="margin-top:1.25rem; padding-top:1rem; border-top:1px solid var(--garis);">
            Akun dibuat oleh Admin. Tidak ada pendaftaran mandiri.
        </p>
    </div>
</div>
</body>
</html>
