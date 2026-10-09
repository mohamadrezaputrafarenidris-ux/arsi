@extends('layouts.app')

@section('judul', 'KRS Menunggu Persetujuan — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>KRS Menunggu Persetujuan</h1>
            <p>Semua Dosen PA melihat seluruh KRS berstatus Menunggu Persetujuan.</p>
        </div>
    </div>

    <div class="panel">
        <div class="panel-judul">Daftar Pengajuan ({{ $krsList->count() }})</div>
        <div class="panel-isi penuh">
            <div class="tabel-bungkus">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Program Studi</th>
                            <th class="tengah">Semester</th>
                            <th class="tengah">Total SKS</th>
                            <th>Diajukan Pada</th>
                            <th class="tengah">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($krsList as $krs)
                            <tr>
                                <td>{{ $krs->mahasiswa?->nim }}</td>
                                <td>{{ $krs->mahasiswa?->nama }}</td>
                                <td>{{ $krs->mahasiswa?->prodi }}</td>
                                <td class="tengah">{{ $krs->mahasiswa?->semester }}</td>
                                <td class="tengah">{{ (int) $krs->mataKuliahs()->sum('sks') }}</td>
                                <td>{{ $krs->diajukan_pada?->format('d-m-Y H:i') ?? '—' }}</td>
                                <td class="tengah">
                                    <a class="tombol tombol-kecil tombol-utama" href="{{ route('dosen.krs.show', $krs) }}">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="kosong">
                                    Belum ada KRS yang perlu diproses.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
