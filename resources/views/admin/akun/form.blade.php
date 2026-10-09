@php
    $mode = $mode ?? 'create';
@endphp

<form method="POST"
      action="{{ $mode === 'edit' ? route('admin.akun.update', $akun) : route('admin.akun.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="panel">
        <div class="panel-judul">{{ $mode === 'edit' ? 'Ubah Akun' : 'Akun Baru' }}</div>
        <div class="panel-isi">
            <div class="formulir-grid">
                <div class="form-grup">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username', $akun->username ?? '') }}" required>
                    @error('username')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
                           @if ($mode === 'create') required @endif>
                    <div class="bantuan">
                        {{ $mode === 'edit' ? 'Kosongkan bila tidak ingin mengubah password.' : 'Minimal 6 karakter.' }}
                    </div>
                    @error('password')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">— pilih role —</option>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $akun->role->value ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="formulir-grid spasi-atas">
                <div class="form-grup">
                    <label for="mahasiswa_id">Data Mahasiswa (untuk role Mahasiswa)</label>
                    <select id="mahasiswa_id" name="mahasiswa_id">
                        <option value="">— tidak dikaitkan —</option>
                        @foreach ($mahasiswas as $m)
                            <option value="{{ $m->id }}" @selected((int) old('mahasiswa_id', $akun->mahasiswa_id ?? 0) === $m->id)>
                                {{ $m->nim }} &mdash; {{ $m->nama }}
                            </option>
                        @endforeach
                    </select>
                    <div class="bantuan">Hanya menampilkan mahasiswa yang belum punya akun.</div>
                    @error('mahasiswa_id')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-grup">
                    <label for="dosen_pa_id">Data Dosen PA (untuk role Dosen PA)</label>
                    <select id="dosen_pa_id" name="dosen_pa_id">
                        <option value="">— tidak dikaitkan —</option>
                        @foreach ($dosenPas as $d)
                            <option value="{{ $d->id }}" @selected((int) old('dosen_pa_id', $akun->dosen_pa_id ?? 0) === $d->id)>
                                {{ $d->nip }} &mdash; {{ $d->nama }}
                            </option>
                        @endforeach
                    </select>
                    <div class="bantuan">Hanya menampilkan dosen PA yang belum punya akun.</div>
                    @error('dosen_pa_id')
                        <div class="error-teks">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-aksi">
                <button type="submit" class="tombol tombol-utama">
                    {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Buat Akun' }}
                </button>
                <a href="{{ route('admin.akun.index') }}" class="tombol">Batal</a>
            </div>
        </div>
    </div>
</form>
