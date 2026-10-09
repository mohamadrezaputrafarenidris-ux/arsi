@extends('layouts.app')

@section('judul', 'Data Mata Kuliah — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Data Mata Kuliah</h1>
            <p>Mata kuliah yang sudah dipakai di KRS tidak dapat dihapus.</p>
        </div>
        <a href="{{ route('admin.matakuliah.create') }}" class="tombol tombol-utama">Tambah Mata Kuliah</a>
    </div>

    <div class="panel">
        <div class="panel-judul">
            <span>Daftar Mata Kuliah</span>
            <form method="GET" action="{{ route('admin.matakuliah.index') }}" class="baris-form" style="margin:0;">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode atau nama" style="min-width:180px;">
                <button type="submit" class="tombol tombol-kecil">Cari</button>
            </form>
        </div>
        <div class="panel-isi penuh">
            <div class="tabel-bungkus">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th class="tengah">SKS</th>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Ruang</th>
                            <th class="tengah">Dipakai KRS</th>
                            <th class="tengah">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mataKuliahs as $mk)
                            <tr>
                                <td>{{ $mk->kode }}</td>
                                <td>{{ $mk->nama }}</td>
                                <td class="tengah">{{ $mk->sks }}</td>
                                <td>{{ $mk->hari }}</td>
                                <td>{{ substr($mk->jam_mulai, 0, 5) }}&ndash;{{ substr($mk->jam_selesai, 0, 5) }}</td>
                                <td>{{ $mk->ruang }}</td>
                                <td class="tengah">{{ $mk->krs_count }}</td>
                                <td class="tengah">
                                    <a class="tombol tombol-kecil" href="{{ route('admin.matakuliah.edit', $mk) }}">Ubah</a>
                                    <form action="{{ route('admin.matakuliah.destroy', $mk) }}" method="POST"
                                          style="display:inline;" onsubmit="return confirm('Hapus mata kuliah {{ $mk->kode }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tombol tombol-kecil tombol-bahaya">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="kosong">
                                    {{ $q !== '' ? 'Mata kuliah tidak ditemukan.' : 'Belum ada data mata kuliah.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:0.75rem 1rem;">
                {{ $mataKuliahs->links() }}
            </div>
        </div>
    </div>
@endsection
