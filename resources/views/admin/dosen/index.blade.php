@extends('layouts.app')

@section('judul', 'Data Dosen PA — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Data Dosen PA</h1>
            <p>Dosen PA menyetujui atau menolak KRS yang diajukan mahasiswa.</p>
        </div>
        <a href="{{ route('admin.dosen.create') }}" class="tombol tombol-utama">Tambah Dosen PA</a>
    </div>

    <div class="panel">
        <div class="panel-judul">
            <span>Daftar Dosen PA</span>
            <form method="GET" action="{{ route('admin.dosen.index') }}" class="baris-form" style="margin:0;">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari NIP atau nama" style="min-width:180px;">
                <button type="submit" class="tombol tombol-kecil">Cari</button>
            </form>
        </div>
        <div class="panel-isi penuh">
            <div class="tabel-bungkus">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th class="tengah">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dosenPas as $d)
                            <tr>
                                <td>{{ $d->nip }}</td>
                                <td>{{ $d->nama }}</td>
                                <td class="tengah">
                                    <a class="tombol tombol-kecil" href="{{ route('admin.dosen.edit', $d) }}">Ubah</a>
                                    <form action="{{ route('admin.dosen.destroy', $d) }}" method="POST"
                                          style="display:inline;" onsubmit="return confirm('Hapus data dosen PA {{ $d->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tombol tombol-kecil tombol-bahaya">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="kosong">
                                    {{ $q !== '' ? 'Dosen PA tidak ditemukan.' : 'Belum ada data dosen PA.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:0.75rem 1rem;">
                {{ $dosenPas->links() }}
            </div>
        </div>
    </div>
@endsection
