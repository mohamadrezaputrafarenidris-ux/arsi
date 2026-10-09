@extends('layouts.app')

@section('judul', 'Tambah Akun — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Tambah Akun</h1>
            <p>Kaitkan akun ke data mahasiswa atau dosen PA yang belum punya akun.</p>
        </div>
        <a href="{{ route('admin.akun.index') }}" class="tombol">Kembali</a>
    </div>

    @include('admin.akun.form', ['mode' => 'create', 'mahasiswas' => $mahasiswas, 'dosenPas' => $dosenPas, 'roleOptions' => $roleOptions])
@endsection
