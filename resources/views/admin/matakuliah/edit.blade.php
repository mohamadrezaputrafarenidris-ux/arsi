@extends('layouts.app')

@section('judul', 'Ubah Mata Kuliah — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Ubah Mata Kuliah</h1>
            <p>{{ $mataKuliah->kode }} &mdash; {{ $mataKuliah->nama }}</p>
        </div>
        <a href="{{ route('admin.matakuliah.index') }}" class="tombol">Kembali</a>
    </div>

    @include('admin.matakuliah.form', ['mode' => 'edit', 'mataKuliah' => $mataKuliah, 'hariOptions' => $hariOptions])
@endsection
