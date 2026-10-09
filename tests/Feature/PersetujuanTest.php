<?php

use App\Enums\KrsStatus;
use App\Models\Krs;

test('halaman kosong menampilkan pesan belum ada KRS', function () {
    [, $akun] = akunDosen();

    $this->actingAs($akun)->get('/dosen-pa/krs-menunggu')
        ->assertOk()
        ->assertSee('Belum ada KRS yang perlu diproses.');
});

test('daftar hanya menampilkan KRS berstatus menunggu persetujuan', function () {
    [$d1] = akunDosen();
    [, $akun] = akunDosen(['nip' => '2222222222222222']);

    [$mhsA] = akunMahasiswa(['nim' => '531424001', 'nama' => 'Andi Pratama']);
    [$mhsB] = akunMahasiswa(['nim' => '531424002']);
    [$mhsC] = akunMahasiswa(['nim' => '531424003']);

    isiKrs($mhsA, [matkul(['sks' => 3])], KrsStatus::MenungguPersetujuan);
    isiKrs($mhsB, [matkul(['sks' => 4])], KrsStatus::Draft);
    isiKrs($mhsC, [matkul(['sks' => 5])], KrsStatus::Disetujui);

    $this->actingAs($akun)->get('/dosen-pa/krs-menunggu')
        ->assertOk()
        ->assertSee('531424001')
        ->assertSee('Andi Pratama')
        ->assertSee('3')
        ->assertDontSee('531424002')
        ->assertDontSee('531424003');
});

test('semua dosen pa melihat semua KRS menunggu', function () {
    [, $akun1] = akunDosen(['nip' => '1111111111111111']);
    [, $akun2] = akunDosen(['nip' => '2222222222222222']);

    [$mhs] = akunMahasiswa();
    isiKrs($mhs, [matkul()], KrsStatus::MenungguPersetujuan);

    $this->actingAs($akun1)->get('/dosen-pa/krs-menunggu')->assertSee($mhs->nim);
    $this->actingAs($akun2)->get('/dosen-pa/krs-menunggu')->assertSee($mhs->nim);
});

test('halaman detail menampilkan identitas dan daftar mata kuliah', function () {
    [, $akun] = akunDosen();
    [$mahasiswa] = akunMahasiswa(['nim' => '531424001', 'nama' => 'Andi Pratama', 'prodi' => 'Sistem Informasi', 'semester' => 5]);
    $mk = matkul(['kode' => 'MK001', 'nama' => 'Pemrograman Web', 'sks' => 3]);
    $krs = isiKrs($mahasiswa, [$mk], KrsStatus::MenungguPersetujuan);

    $this->actingAs($akun)->get("/dosen-pa/krs-menunggu/{$krs->id}")
        ->assertOk()
        ->assertSee('531424001')
        ->assertSee('Andi Pratama')
        ->assertSee('Sistem Informasi')
        ->assertSee('MK001')
        ->assertSee('Pemrograman Web')
        ->assertSee('Menunggu Persetujuan');
});

test('dosen pa bisa menyetujui KRS', function () {
    [, $akun] = akunDosen();
    [$mahasiswa] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, [matkul()], KrsStatus::MenungguPersetujuan);

    $this->actingAs($akun)->post("/dosen-pa/krs-menunggu/{$krs->id}/setujui")
        ->assertRedirect(route('dosen.pending.index'));

    $krs->refresh();
    expect($krs->status)->toBe(KrsStatus::Disetujui)
        ->and($krs->diputuskan_pada)->not->toBeNull();
});

test('dosen pa bisa menolak KRS tanpa alasan', function () {
    [, $akun] = akunDosen();
    [$mahasiswa] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, [matkul()], KrsStatus::MenungguPersetujuan);

    $this->actingAs($akun)->post("/dosen-pa/krs-menunggu/{$krs->id}/tolak")
        ->assertRedirect(route('dosen.pending.index'));

    expect($krs->refresh()->status)->toBe(KrsStatus::Ditolak);
});

test('keputusan hanya boleh pada KRS menunggu persetujuan', function (KrsStatus $status) {
    [, $akun] = akunDosen();
    [$mahasiswa] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, [matkul()], $status);

    $this->actingAs($akun)->post("/dosen-pa/krs-menunggu/{$krs->id}/setujui")
        ->assertSessionHasErrors('krs');

    $this->actingAs($akun)->post("/dosen-pa/krs-menunggu/{$krs->id}/tolak")
        ->assertSessionHasErrors('krs');

    expect($krs->refresh()->status)->toBe($status);
})->with([KrsStatus::Draft, KrsStatus::Disetujui, KrsStatus::Ditolak]);

test('keputusan akhir tidak bisa diubah lagi oleh dosen', function (KrsStatus $awal, KrsStatus $target) {
    [, $akun] = akunDosen();
    [$mahasiswa] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, [matkul()], $awal);

    $aksi = $target === KrsStatus::Disetujui ? 'tolak' : 'setujui';

    $this->actingAs($akun)->post("/dosen-pa/krs-menunggu/{$krs->id}/{$aksi}")
        ->assertSessionHasErrors('krs');

    expect($krs->refresh()->status)->toBe($awal);
})->with([
    [KrsStatus::Disetujui, KrsStatus::Disetujui],
    [KrsStatus::Ditolak, KrsStatus::Ditolak],
]);

test('keputusan dosen memakai transaksi dan status berubah tepat satu kali', function () {
    [, $akun] = akunDosen();
    [$mahasiswa] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, [matkul()], KrsStatus::MenungguPersetujuan);

    $this->actingAs($akun)->post("/dosen-pa/krs-menunggu/{$krs->id}/setujui")->assertRedirect();
    $this->actingAs($akun)->post("/dosen-pa/krs-menunggu/{$krs->id}/tolak")->assertSessionHasErrors('krs');

    $krs->refresh();
    expect($krs->status)->toBe(KrsStatus::Disetujui)
        ->and(Krs::count())->toBe(1);
});

test('halaman detail bisa dibuka dosen untuk KRS non-menunggu', function () {
    [, $akun] = akunDosen();
    [$mahasiswa] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, [matkul()], KrsStatus::Disetujui);

    $this->actingAs($akun)->get("/dosen-pa/krs-menunggu/{$krs->id}")
        ->assertOk()
        ->assertSee('Disetujui')
        ->assertDontSee('Setujui');
});
