@extends('layouts.app')

@section('judul', 'Ubah Akun — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Ubah Akun</h1>
            <p>Kosongkan password jika tidak ingin mengubahnya.</p>
        </div>
        <a href="{{ route('admin.akun.index') }}" class="tombol">Kembali</a>
    </div>

    @include('admin.akun.form', ['mode' => 'edit', 'akun' => $akun, 'mahasiswas' => $mahasiswas, 'dosenPas' => $dosenPas, 'roleOptions' => $roleOptions])
@endsection
