<?php

use App\Enums\KrsStatus;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Services\KrsValidator;
use Illuminate\Support\ViewErrorBag;

/**
 * Ambil semua pesan kesalahan dari sesi setelah pengajuan.
 * Sesi menyimpan errors sebagai ViewErrorBag maupun array tergantung jalur flash.
 *
 * @return array<int, string>
 */
function pesanKrs($response): array
{
    $errors = session('errors');

    if ($errors instanceof ViewErrorBag) {
        return $errors->get('krs', []);
    }

    if (is_array($errors)) {
        return $errors['default']['messages']['krs']
            ?? $errors['messages']['krs']
            ?? $errors['krs']
            ?? [];
    }

    return [];
}

test('pengajuan kosong ditolak: minimal 1 mata kuliah', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, []);

    $response = $this->actingAs($akun)->post('/mahasiswa/krs/ajukan');

    $response->assertSessionHasErrors('krs');
    expect(pesanKrs($response))->toContain('KRS minimal harus berisi 1 mata kuliah.');
    expect($krs->refresh()->status)->toBe(KrsStatus::Draft);
});

test('pengajuan melebihi 24 SKS ditolak', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    // 9 mata kuliah 3 SKS pada slot berbeda (27 SKS) supaya hanya melanggar aturan SKS.
    $mataKuliahs = collect(range(0, 8))->map(fn (int $i) => matkul([
        'sks' => 3,
        'hari' => ['Senin', 'Selasa', 'Rabu', 'Kamis'][$i % 4],
        'jam_mulai' => intdiv($i, 4) === 0 ? '07:00' : '13:00',
        'jam_selesai' => intdiv($i, 4) === 0 ? '09:00' : '15:00',
    ]));
    $krs = isiKrs($mahasiswa, $mataKuliahs->all());

    $response = $this->actingAs($akun)->post('/mahasiswa/krs/ajukan');

    expect(pesanKrs($response))
        ->not->toBeEmpty()
        ->and(implode(' ', pesanKrs($response)))->toContain('melebihi batas maksimal 24 SKS');
    expect($krs->refresh()->status)->toBe(KrsStatus::Draft);
});

test('pengajuan tepat 24 SKS diterima', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    // 8 mata kuliah non-bertabrakan: 4 hari x 2 slot waktu.
    $mataKuliahs = collect(range(0, 7))->map(fn (int $i) => matkul([
        'sks' => 3,
        'hari' => ['Senin', 'Selasa', 'Rabu', 'Kamis'][$i % 4],
        'jam_mulai' => intdiv($i, 4) === 0 ? '07:00' : '13:00',
        'jam_selesai' => intdiv($i, 4) === 0 ? '09:00' : '15:00',
    ]));
    isiKrs($mahasiswa, $mataKuliahs->all());

    $this->actingAs($akun)->post('/mahasiswa/krs/ajukan')
        ->assertRedirect(route('mahasiswa.krs.index'));

    expect(Krs::where('mahasiswa_id', $mahasiswa->id)->first()->status)
        ->toBe(KrsStatus::MenungguPersetujuan);
});

test('pasangan mata kuliah bentrok ditolak dan pasangannya disebut', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    $a = matkul(['kode' => 'MK001', 'nama' => 'Pemrograman Web', 'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:30']);
    $b = matkul(['kode' => 'MK002', 'nama' => 'Basis Data', 'hari' => 'Senin', 'jam_mulai' => '10:00', 'jam_selesai' => '12:30']);
    $krs = isiKrs($mahasiswa, [$a, $b]);

    $response = $this->actingAs($akun)->post('/mahasiswa/krs/ajukan');

    $pesan = implode(' ', pesanKrs($response));
    expect($pesan)->toContain('bentrok')
        ->and($pesan)->toContain('MK001')
        ->and($pesan)->toContain('MK002');
    expect($krs->refresh()->status)->toBe(KrsStatus::Draft);
});

test('mata kuliah berurutan pada hari yang sama bukan bentrok', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    $a = matkul(['kode' => 'MK003', 'hari' => 'Selasa', 'jam_mulai' => '08:00', 'jam_selesai' => '10:30']);
    $b = matkul(['kode' => 'MK004', 'hari' => 'Selasa', 'jam_mulai' => '10:30', 'jam_selesai' => '13:00']);
    isiKrs($mahasiswa, [$a, $b]);

    $response = $this->actingAs($akun)->post('/mahasiswa/krs/ajukan');

    $response->assertRedirect(route('mahasiswa.krs.index'));
    expect(pesanKrs($response))->toBeEmpty()
        ->and(Krs::where('mahasiswa_id', $mahasiswa->id)->first()->status)
        ->toBe(KrsStatus::MenungguPersetujuan);
});

test('mata kuliah pada hari berbeda tidak dianggap bentrok', function () {
    $a = matkul(['hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);
    $b = matkul(['hari' => 'Rabu', 'jam_mulai' => '09:00', 'jam_selesai' => '11:00']);

    expect(app(KrsValidator::class)->isConflict($a, $b))->toBeFalse();
});

test('semua kesalahan ditampilkan sekaligus', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    // 8 mata kuliah 3 SKS pada slot non-bertabrakan (24 SKS) + dua yang bentrok (30 SKS),
    // sehingga pelanggaran SKS dan bentrok sama-sama muncul.
    $mataKuliahs = collect(range(0, 7))->map(fn (int $i) => matkul([
        'sks' => 3,
        'hari' => ['Senin', 'Selasa', 'Rabu', 'Kamis'][$i % 4],
        'jam_mulai' => intdiv($i, 4) === 0 ? '07:00' : '13:00',
        'jam_selesai' => intdiv($i, 4) === 0 ? '09:00' : '15:00',
    ]))->all();
    $mataKuliahs[] = matkul([
        'sks' => 3, 'hari' => 'Senin',
        'jam_mulai' => '08:00', 'jam_selesai' => '10:00',
    ]);
    $mataKuliahs[] = matkul([
        'sks' => 3, 'hari' => 'Senin',
        'jam_mulai' => '09:00', 'jam_selesai' => '11:00',
    ]);
    $krs = isiKrs($mahasiswa, $mataKuliahs);

    $response = $this->actingAs($akun)->post('/mahasiswa/krs/ajukan');

    $pesan = implode(' ', pesanKrs($response));
    expect($pesan)->toContain('melebihi batas maksimal 24 SKS')
        ->and($pesan)->toContain('bentrok');
    expect($krs->refresh()->status)->toBe(KrsStatus::Draft);
});

test('KrsValidator melaporkan duplikat mata kuliah', function () {
    // Duplikat tidak bisa terjadi lewat pivot (unique constraint), jadi diuji langsung
    // terhadap kumpulan yang memuat dua entri berkode sama.
    $a = MataKuliah::factory()->make(['kode' => 'MKDUP']);
    $b = MataKuliah::factory()->make(['kode' => 'MKDUP']);

    $errors = app(KrsValidator::class)->validateCollection(collect([$a, $b]));

    expect(implode(' ', $errors))->toContain('MKDUP');
});

test('KrsValidator melaporkan semua kategori kesalahan sekaligus', function () {
    $validator = app(KrsValidator::class);
    $a = matkul(['kode' => 'MKX', 'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);
    $b = matkul(['kode' => 'MKY', 'hari' => 'Senin', 'jam_mulai' => '09:00', 'jam_selesai' => '11:00']);

    $errors = $validator->validateCollection(collect([$a, $b, $b]));
    $pesan = implode(' ', $errors);

    // Duplikat dan bentrok sama-sama dilaporkan, status tetap Draft.
    expect($pesan)->toContain('MKY')
        ->and($pesan)->toContain('bentrok');
});

test('KrsValidator menerima KRS valid tanpa kesalahan', function () {
    [$mahasiswa] = akunMahasiswa();
    $a = matkul(['kode' => 'MKZ1', 'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);
    $b = matkul(['kode' => 'MKZ2', 'hari' => 'Senin', 'jam_mulai' => '10:00', 'jam_selesai' => '12:00']);
    $krs = isiKrs($mahasiswa, [$a, $b]);

    expect(app(KrsValidator::class)->validate($krs))->toBe([]);
});
