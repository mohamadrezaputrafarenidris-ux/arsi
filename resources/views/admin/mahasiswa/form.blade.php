@php
    $mode = $mode ?? 'create';
    $mahasiswa = $mahasiswa ?? null;
@endphp

<form method="POST"
      action="{{ $mode === 'edit' ? route('admin.mahasiswa.update', $mahasiswa) : route('admin.mahasiswa.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="panel">
        <div class="panel-judul">{{ $mode === 'edit' ? 'Ubah Data Mahasiswa' : 'Mahasiswa Baru' }}</div>
        <div class="panel-isi">
            <div class="formulir-grid">
                <div class="form-grup">
                    <label for="nim">NIM</label>
                    <input type="text" id="nim" name="nim" value="{{ old('nim', $mahasiswa->nim ?? '') }}" required>
                    @error('nim')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $mahasiswa->nama ?? '') }}" required>
                    @error('nama')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="prodi">Program Studi</label>
                    <input type="text" id="prodi" name="prodi" value="{{ old('prodi', $mahasiswa->prodi ?? 'Sistem Informasi') }}" required>
                    @error('prodi')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="semester">Semester</label>
                    <input type="number" id="semester" name="semester" min="1" max="14"
                           value="{{ old('semester', $mahasiswa->semester ?? 1) }}" required>
                    @error('semester')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-aksi">
                <button type="submit" class="tombol tombol-utama">
                    {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Mahasiswa' }}
                </button>
                <a href="{{ route('admin.mahasiswa.index') }}" class="tombol">Batal</a>
            </div>
        </div>
    </div>
</form>
