@extends('layouts.app')

@section('judul', 'Ubah Mahasiswa — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Ubah Data Mahasiswa</h1>
            <p>{{ $mahasiswa->nim }} &mdash; {{ $mahasiswa->nama }}</p>
        </div>
        <a href="{{ route('admin.mahasiswa.index') }}" class="tombol">Kembali</a>
    </div>

    @include('admin.mahasiswa.form', ['mode' => 'edit', 'mahasiswa' => $mahasiswa])
@endsection
