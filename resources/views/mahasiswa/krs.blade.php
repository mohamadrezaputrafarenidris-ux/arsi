@extends('layouts.app')

@section('judul', 'KRS Saya — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Kartu Rencana Studi</h1>
            <p>
                {{ $mahasiswa->nim }} &middot; {{ $mahasiswa->nama }} &middot;
                {{ $mahasiswa->prodi }} &middot; Semester {{ $mahasiswa->semester }}
            </p>
        </div>
        <div>
            Status: <span class="badge {{ $krs->status->badgeClass() }}">{{ $krs->status->label() }}</span>
        </div>
    </div>

    @if (! $krs->status->isEditable())
        <div class="pesan pesan-sukses">
            KRS berstatus <strong>{{ $krs->status->label() }}</strong>
            @if ($krs->diajukan_pada)
                sejak {{ $krs->diajukan_pada->format('d-m-Y H:i') }}
            @endif
            . Isi KRS terkunci (mode baca) dan tidak dapat diubah lagi.
        </div>
    @endif

    <div class="panel">
        <div class="panel-judul">
            <span>Daftar Mata Kuliah</span>
            <span>
                Total SKS:
                <strong>{{ $totalSks }}</strong>
                @if ($krs->status->isEditable())
                    <span class="teks-redup">/ maksimal {{ $sisaSks }}</span>
                @endif
            </span>
        </div>
        <div class="panel-isi penuh">
            <div class="tabel-bungkus">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th class="tengah">SKS</th>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Ruang</th>
                            @if ($krs->status->isEditable())
                                <th class="tengah">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($krs->mataKuliahs as $mk)
                            <tr>
                                <td>{{ $mk->kode }}</td>
                                <td>{{ $mk->nama }}</td>
                                <td class="tengah">{{ $mk->sks }}</td>
                                <td>{{ $mk->hari }}</td>
                                <td>{{ substr($mk->jam_mulai, 0, 5) }}&ndash;{{ substr($mk->jam_selesai, 0, 5) }}</td>
                                <td>{{ $mk->ruang }}</td>
                                @if ($krs->status->isEditable())
                                    <td class="tengah">
                                        <form action="{{ route('mahasiswa.krs.destroy', $mk) }}" method="POST"
                                              onsubmit="return confirm('Hapus {{ $mk->kode }} dari KRS?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tombol tombol-kecil tombol-bahaya">Hapus</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $krs->status->isEditable() ? 7 : 6 }}" class="kosong">
                                    Belum ada mata kuliah dipilih. Tambahkan dari daftar di bawah.
                                </td>
                            </tr>
                        @endforelse
                        <tr class="baris-total">
                            <td colspan="2">Total</td>
                            <td class="tengah">{{ $totalSks }}</td>
                            <td colspan="{{ $krs->status->isEditable() ? 4 : 3 }}"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($krs->status->isEditable())
        <div class="panel">
            <div class="panel-judul">Tambah Mata Kuliah</div>
            <div class="panel-isi">
                <form method="POST" action="{{ route('mahasiswa.krs.store') }}" class="baris-form">
                    @csrf
                    <div class="form-grup">
                        <label for="mata_kuliah_id">Mata Kuliah Tersedia</label>
                        <select id="mata_kuliah_id" name="mata_kuliah_id" required>
                            <option value="">— pilih mata kuliah —</option>
                            @foreach ($tersedia as $mk)
                                <option value="{{ $mk->id }}">
                                    {{ $mk->kode }} &mdash; {{ $mk->nama }} ({{ $mk->sks }} SKS,
                                    {{ $mk->hari }} {{ substr($mk->jam_mulai, 0, 5) }}&ndash;{{ substr($mk->jam_selesai, 0, 5) }},
                                    {{ $mk->ruang }})
                                </option>
                            @endforeach
                        </select>
                        @error('mata_kuliah_id')
                            <div class="error-teks">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="tombol">Tambahkan</button>
                </form>

                @if ($tersedia->isEmpty())
                    <p class="teks-kecil teks-redup" style="margin-top:0.75rem; margin-bottom:0;">
                        Semua mata kuliah sudah ditambahkan ke KRS.
                    </p>
                @endif

                <div class="form-aksi">
                    <form action="{{ route('mahasiswa.krs.ajukan') }}" method="POST"
                          onsubmit="return confirm('Ajukan KRS ini ke Dosen PA? Pastikan tidak ada jadwal bentrok.');">
                        @csrf
                        <button type="submit" class="tombol tombol-utama">Ajukan KRS</button>
                    </form>
                    <span class="teks-kecil teks-redup">
                        Setelah diajukan, isi KRS terkunci dan menunggu persetujuan Dosen PA.
                    </span>
                </div>
            </div>
        </div>
    @endif

    <div class="panel">
        <div class="panel-judul">Keterangan</div>
        <div class="panel-isi">
            <p class="teks-kecil" style="margin-bottom:0.5rem;">Siklus status KRS:</p>
            <p class="teks-kecil" style="margin-bottom:0;">
                <span class="badge badge-draft">Draft</span> &rarr;
                <span class="badge badge-menunggu">Menunggu Persetujuan</span> &rarr;
                <span class="badge badge-disetujui">Disetujui</span> /
                <span class="badge badge-ditolak">Ditolak</span>.
                Status Disetujui dan Ditolak bersifat final.
            </p>
        </div>
    </div>
@endsection
