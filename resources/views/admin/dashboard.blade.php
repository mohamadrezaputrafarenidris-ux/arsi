@extends('layouts.app')

@section('judul', 'Dashboard — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Dashboard</h1>
            <p>Ringkasan data akademik dan status KRS. Admin tidak menyusun KRS.</p>
        </div>
    </div>

    <div class="ringkas">
        <div class="ringkas-item">
            <div class="nilai">{{ $jumlahMahasiswa }}</div>
            <div class="label">Mahasiswa</div>
        </div>
        <div class="ringkas-item">
            <div class="nilai">{{ $jumlahDosen }}</div>
            <div class="label">Dosen PA</div>
        </div>
        <div class="ringkas-item">
            <div class="nilai">{{ $jumlahMataKuliah }}</div>
            <div class="label">Mata Kuliah</div>
        </div>
        <div class="ringkas-item">
            <div class="nilai">{{ $jumlahKrs }}</div>
            <div class="label">Total KRS</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-judul">KRS per Status</div>
        <div class="panel-isi penuh">
            <div class="tabel-bungkus">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="angka">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Enums\KrsStatus::cases() as $status)
                            <tr>
                                <td><span class="badge {{ $status->badgeClass() }}">{{ $status->label() }}</span></td>
                                <td class="angka">{{ $krsPerStatus[$status->value] ?? 0 }}</td>
                            </tr>
                        @endforeach
                        <tr class="baris-total">
                            <td>Total</td>
                            <td class="angka">{{ $jumlahKrs }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-judul">Kelola Data</div>
        <div class="panel-isi">
            <p class="teks-kecil" style="margin-bottom:0.75rem;">
                Semua akun dibuat di sini; tidak ada pendaftaran mandiri.
            </p>
            <div class="baris-form">
                <a class="tombol" href="{{ route('admin.mahasiswa.index') }}">Data Mahasiswa</a>
                <a class="tombol" href="{{ route('admin.dosen.index') }}">Data Dosen PA</a>
                <a class="tombol" href="{{ route('admin.matakuliah.index') }}">Data Mata Kuliah</a>
                <a class="tombol tombol-utama" href="{{ route('admin.akun.index') }}">Kelola Akun</a>
            </div>
        </div>
    </div>
@endsection
