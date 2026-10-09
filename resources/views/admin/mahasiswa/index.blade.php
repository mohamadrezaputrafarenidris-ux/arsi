@extends('layouts.app')

@section('judul', 'Data Mahasiswa — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Data Mahasiswa</h1>
            <p>Mahasiswa dengan KRS yang sudah diajukan atau diputuskan tidak dapat dihapus.</p>
        </div>
        <a href="{{ route('admin.mahasiswa.create') }}" class="tombol tombol-utama">Tambah Mahasiswa</a>
    </div>

    <div class="panel">
        <div class="panel-judul">
            <span>Daftar Mahasiswa</span>
            <form method="GET" action="{{ route('admin.mahasiswa.index') }}" class="baris-form" style="margin:0;">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari NIM atau nama" style="min-width:180px;">
                <button type="submit" class="tombol tombol-kecil">Cari</button>
            </form>
        </div>
        <div class="panel-isi penuh">
            <div class="tabel-bungkus">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Program Studi</th>
                            <th class="tengah">Semester</th>
                            <th class="tengah">Status KRS</th>
                            <th class="tengah">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mahasiswas as $m)
                            <tr>
                                <td>{{ $m->nim }}</td>
                                <td>{{ $m->nama }}</td>
                                <td>{{ $m->prodi }}</td>
                                <td class="tengah">{{ $m->semester }}</td>
                                <td class="tengah">
                                    @if ($m->krs)
                                        <span class="badge {{ $m->krs->status->badgeClass() }}">{{ $m->krs->status->label() }}</span>
                                    @else
                                        <span class="teks-redup">Belum ada KRS</span>
                                    @endif
                                </td>
                                <td class="tengah">
                                    <a class="tombol tombol-kecil" href="{{ route('admin.mahasiswa.edit', $m) }}">Ubah</a>
                                    <form action="{{ route('admin.mahasiswa.destroy', $m) }}" method="POST"
                                          style="display:inline;" onsubmit="return confirm('Hapus data mahasiswa {{ $m->nama }}? Jika masih Draft, KRS ikut terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tombol tombol-kecil tombol-bahaya">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="kosong">
                                    {{ $q !== '' ? 'Mahasiswa tidak ditemukan.' : 'Belum ada data mahasiswa.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:0.75rem 1rem;">
                {{ $mahasiswas->links() }}
            </div>
        </div>
    </div>
@endsection
