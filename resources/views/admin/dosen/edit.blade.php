@extends('layouts.app')

@section('judul', 'Ubah Dosen PA — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Ubah Data Dosen PA</h1>
            <p>NIP {{ $dosen->nip }} &mdash; {{ $dosen->nama }}</p>
        </div>
        <a href="{{ route('admin.dosen.index') }}" class="tombol">Kembali</a>
    </div>

    @include('admin.dosen.form', ['mode' => 'edit', 'dosen' => $dosen])
@endsection
