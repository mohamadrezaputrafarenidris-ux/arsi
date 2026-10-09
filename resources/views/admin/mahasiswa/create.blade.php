@extends('layouts.app')

@section('judul', 'Tambah Mahasiswa — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Tambah Mahasiswa</h1>
            <p>NIM wajib unik. Akun login dibuat terpisah melalui menu Akun.</p>
        </div>
        <a href="{{ route('admin.mahasiswa.index') }}" class="tombol">Kembali</a>
    </div>

    @include('admin.mahasiswa.form', ['mode' => 'create'])
@endsection
