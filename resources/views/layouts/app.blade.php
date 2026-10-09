<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Sistem Pengajuan dan Persetujuan KRS')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        /* Cadangan bila aset belum dibangun: tampilan tetap terbaca. */
        @if (! file_exists(public_path('build/manifest.json')) && ! file_exists(public_path('hot')))
            body { font-family: "IBM Plex Sans", "Segoe UI", system-ui, sans-serif; background: #f6f5f1; color: #1b1b18; margin: 0; }
        @endif
    </style>
</head>
<body>
<header class="bar-atas">
    <div class="bar-atas-inner">
        <div class="merek">
            Sistem KRS
            <span>Pengajuan &amp; Persetujuan</span>
        </div>

        <nav class="navigasi">
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'aktif' : '' }}">Dashboard</a>
                <a href="{{ route('admin.mahasiswa.index') }}" class="{{ request()->routeIs('admin.mahasiswa.*') ? 'aktif' : '' }}">Mahasiswa</a>
                <a href="{{ route('admin.dosen.index') }}" class="{{ request()->routeIs('admin.dosen.*') ? 'aktif' : '' }}">Dosen PA</a>
                <a href="{{ route('admin.matakuliah.index') }}" class="{{ request()->routeIs('admin.matakuliah.*') ? 'aktif' : '' }}">Mata Kuliah</a>
                <a href="{{ route('admin.akun.index') }}" class="{{ request()->routeIs('admin.akun.*') ? 'aktif' : '' }}">Akun</a>
            @elseif (auth()->user()->role === \App\Enums\Role::Mahasiswa)
                <a href="{{ route('mahasiswa.krs.index') }}" class="{{ request()->routeIs('mahasiswa.krs.*') ? 'aktif' : '' }}">KRS Saya</a>
            @else
                <a href="{{ route('dosen.pending.index') }}" class="{{ request()->routeIs('dosen.*') ? 'aktif' : '' }}">KRS Menunggu Persetujuan</a>
            @endif
        </nav>

        <div class="identitas">
            <strong>{{ auth()->user()->username }}</strong>
            &middot; {{ auth()->user()->role->label() }}
            <form action="{{ route('logout') }}" method="POST" style="display:inline; margin-left:0.6rem;">
                @csrf
                <button type="submit" class="tombol tombol-kecil">Keluar</button>
            </form>
        </div>
    </div>
</header>

<main class="wadah">
    @if (session('success'))
        <div class="pesan pesan-sukses">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="pesan pesan-gagal">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="pesan pesan-gagal">
            <strong>Terjadi kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('konten')
</main>
</body>
</html>
