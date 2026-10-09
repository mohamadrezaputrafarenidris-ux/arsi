@php
    $mode = $mode ?? 'create';
    $mataKuliah = $mataKuliah ?? null;
@endphp

<form method="POST"
      action="{{ $mode === 'edit' ? route('admin.matakuliah.update', $mataKuliah) : route('admin.matakuliah.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="panel">
        <div class="panel-judul">{{ $mode === 'edit' ? 'Ubah Mata Kuliah' : 'Mata Kuliah Baru' }}</div>
        <div class="panel-isi">
            <div class="formulir-grid">
                <div class="form-grup">
                    <label for="kode">Kode</label>
                    <input type="text" id="kode" name="kode" value="{{ old('kode', $mataKuliah->kode ?? '') }}" required>
                    @error('kode')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="nama">Nama Mata Kuliah</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $mataKuliah->nama ?? '') }}" required>
                    @error('nama')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="sks">SKS (1&ndash;6)</label>
                    <input type="number" id="sks" name="sks" min="1" max="6"
                           value="{{ old('sks', $mataKuliah->sks ?? 3) }}" required>
                    @error('sks')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="hari">Hari</label>
                    <select id="hari" name="hari" required>
                        <option value="">— pilih hari —</option>
                        @foreach ($hariOptions as $hari)
                            <option value="{{ $hari }}" @selected(old('hari', $mataKuliah->hari ?? '') === $hari)>{{ $hari }}</option>
                        @endforeach
                    </select>
                    @error('hari')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="jam_mulai">Jam Mulai</label>
                    <input type="time" id="jam_mulai" name="jam_mulai"
                           value="{{ old('jam_mulai', $mataKuliah ? substr($mataKuliah->jam_mulai, 0, 5) : '08:00') }}" required>
                    @error('jam_mulai')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="jam_selesai">Jam Selesai</label>
                    <input type="time" id="jam_selesai" name="jam_selesai"
                           value="{{ old('jam_selesai', $mataKuliah ? substr($mataKuliah->jam_selesai, 0, 5) : '10:00') }}" required>
                    <div class="bantuan">Wajib lebih besar dari jam mulai.</div>
                    @error('jam_selesai')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="ruang">Ruang</label>
                    <input type="text" id="ruang" name="ruang" value="{{ old('ruang', $mataKuliah->ruang ?? '') }}" required>
                    @error('ruang')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-aksi">
                <button type="submit" class="tombol tombol-utama">
                    {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Mata Kuliah' }}
                </button>
                <a href="{{ route('admin.matakuliah.index') }}" class="tombol">Batal</a>
            </div>
        </div>
    </div>
</form>
