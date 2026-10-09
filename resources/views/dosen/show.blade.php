@extends('layouts.app')

@section('judul', 'Detail KRS — Sistem KRS')

@section('konten')
    <div class="kepala-halaman">
        <div>
            <h1>Detail KRS</h1>
            <p>Tinjau daftar mata kuliah sebelum memberikan keputusan.</p>
        </div>
        <a href="{{ route('dosen.pending.index') }}" class="tombol">&larr; Kembali</a>
    </div>

    <div class="panel">
        <div class="panel-judul">
            <span>Identitas Mahasiswa</span>
            <span>Status: <span class="badge {{ $krs->status->badgeClass() }}">{{ $krs->status->label() }}</span></span>
        </div>
        <div class="panel-isi">
            <div class="identitas-grid">
                <div>
                    <div class="kunci">NIM</div>
                    <div class="nilai">{{ $krs->mahasiswa?->nim }}</div>
                </div>
                <div>
                    <div class="kunci">Nama</div>
                    <div class="nilai">{{ $krs->mahasiswa?->nama }}</div>
                </div>
                <div>
                    <div class="kunci">Program Studi</div>
                    <div class="nilai">{{ $krs->mahasiswa?->prodi }}</div>
                </div>
                <div>
                    <div class="kunci">Semester</div>
                    <div class="nilai">{{ $krs->mahasiswa?->semester }}</div>
                </div>
                <div>
                    <div class="kunci">Diajukan Pada</div>
                    <div class="nilai">{{ $krs->diajukan_pada?->format('d-m-Y H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div class="kunci">Diputuskan Pada</div>
                    <div class="nilai">{{ $krs->diputuskan_pada?->format('d-m-Y H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-judul">
            <span>Daftar Mata Kuliah</span>
            <span>Total SKS: <strong>{{ (int) $krs->mataKuliahs->sum('sks') }}</strong></span>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="kosong">KRS tidak berisi mata kuliah.</td>
                            </tr>
                        @endforelse
                        <tr class="baris-total">
                            <td colspan="2">Total</td>
                            <td class="tengah">{{ (int) $krs->mataKuliahs->sum('sks') }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($krs->status === \App\Enums\KrsStatus::MenungguPersetujuan)
        <div class="panel">
            <div class="panel-judul">Keputusan</div>
            <div class="panel-isi">
                <div class="baris-form">
                    <form action="{{ route('dosen.krs.setujui', $krs) }}" method="POST"
                          onsubmit="return confirm('Setujui KRS {{ $krs->mahasiswa?->nama }}?');">
                        @csrf
                        <button type="submit" class="tombol tombol-utama">Setujui</button>
                    </form>
                    <form action="{{ route('dosen.krs.tolak', $krs) }}" method="POST"
                          onsubmit="return confirm('Tolak KRS {{ $krs->mahasiswa?->nama }}?');">
                        @csrf
                        <button type="submit" class="tombol tombol-bahaya">Tolak</button>
                    </form>
                </div>
                <p class="teks-kecil teks-redup" style="margin:0.9rem 0 0;">
                    Keputusan bersifat final: KRS yang sudah Disetujui atau Ditolak tidak dapat diubah lagi.
                </p>
            </div>
        </div>
    @else
        <div class="pesan pesan-sukses">
            KRS ini sudah berstatus <strong>{{ $krs->status->label() }}</strong> dan tidak dapat diubah lagi.
        </div>
    @endif
@endsection
