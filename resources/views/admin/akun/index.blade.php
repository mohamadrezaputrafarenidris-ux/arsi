@extends('layouts.app')

@section('judul', 'Kelola Akun — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Kelola Akun</h1>
            <p>Akun login untuk Admin, Mahasiswa, dan Dosen PA. Menghapus akun tidak menghapus data mahasiswa/dosen.</p>
        </div>
        <a href="{{ route('admin.akun.create') }}" class="tombol tombol-utama">Tambah Akun</a>
    </div>

    <div class="panel">
        <div class="panel-judul">Daftar Akun ({{ $akuns->count() }})</div>
        <div class="panel-isi penuh">
            <div class="tabel-bungkus">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Data Terkait</th>
                            <th class="tengah">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($akuns as $akun)
                            <tr>
                                <td>{{ $akun->username }}</td>
                                <td>{{ $akun->role->label() }}</td>
                                <td class="teks-redup">
                                    @if ($akun->mahasiswa)
                                        {{ $akun->mahasiswa->nim }} &mdash; {{ $akun->mahasiswa->nama }}
                                    @elseif ($akun->dosenPa)
                                        NIP {{ $akun->dosenPa->nip }} &mdash; {{ $akun->dosenPa->nama }}
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td class="tengah">
                                    <a class="tombol tombol-kecil" href="{{ route('admin.akun.edit', $akun) }}">Ubah</a>
                                    @unless ($akun->is(auth()->user()))
                                        <form action="{{ route('admin.akun.destroy', $akun) }}" method="POST"
                                              style="display:inline;" onsubmit="return confirm('Hapus akun {{ $akun->username }}? Data mahasiswa/dosen tidak ikut terhapus.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tombol tombol-kecil tombol-bahaya">Hapus</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="kosong">Belum ada akun terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
