@extends('layouts.app')

@section('judul', 'Tambah Mata Kuliah — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Tambah Mata Kuliah</h1>
            <p>Kode wajib unik, SKS antara 1&ndash;6, hari Senin&ndash;Sabtu.</p>
        </div>
        <a href="{{ route('admin.matakuliah.index') }}" class="tombol">Kembali</a>
    </div>

    @include('admin.matakuliah.form', ['mode' => 'create', 'hariOptions' => $hariOptions])
@endsection
