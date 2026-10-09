@extends('layouts.app')

@section('judul', 'Tambah Dosen PA — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Tambah Dosen PA</h1>
            <p>NIP wajib unik. Akun login dibuat terpisah melalui menu Akun.</p>
        </div>
        <a href="{{ route('admin.dosen.index') }}" class="tombol">Kembali</a>
    </div>

    @include('admin.dosen.form', ['mode' => 'create'])
@endsection
