@php
    $mode = $mode ?? 'create';
    $dosen = $dosen ?? null;
@endphp

<form method="POST"
      action="{{ $mode === 'edit' ? route('admin.dosen.update', $dosen) : route('admin.dosen.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="panel">
        <div class="panel-judul">{{ $mode === 'edit' ? 'Ubah Data Dosen PA' : 'Dosen PA Baru' }}</div>
        <div class="panel-isi">
            <div class="formulir-grid">
                <div class="form-grup">
                    <label for="nip">NIP</label>
                    <input type="text" id="nip" name="nip" value="{{ old('nip', $dosen->nip ?? '') }}" required>
                    @error('nip')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="nama">Nama Lengkap (gelar)</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $dosen->nama ?? '') }}" required>
                    @error('nama')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-aksi">
                <button type="submit" class="tombol tombol-utama">
                    {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Dosen PA' }}
                </button>
                <a href="{{ route('admin.dosen.index') }}" class="tombol">Batal</a>
            </div>
        </div>
    </div>
</form>
