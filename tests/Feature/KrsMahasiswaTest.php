<?php

use App\Enums\KrsStatus;
use App\Models\Krs;
use Illuminate\Database\QueryException;

test('halaman KRS membuat KRS draft otomatis untuk mahasiswa login', function () {
    [$mahasiswa, $akun] = akunMahasiswa();

    $this->actingAs($akun)->get('/mahasiswa/krs')
        ->assertOk()
        ->assertSee($mahasiswa->nim);

    $this->assertDatabaseHas('krs', [
        'mahasiswa_id' => $mahasiswa->id,
        'status' => KrsStatus::Draft->value,
    ]);

    // Satu mahasiswa = satu KRS: dibuka lagi tidak membuat KRS baru.
    $this->actingAs($akun)->get('/mahasiswa/krs')->assertOk();
    expect(Krs::count())->toBe(1);
});

test('KRS diambil dari akun login, bukan dari parameter URL', function () {
    [$mahasiswaA, $akunA] = akunMahasiswa(['nim' => '531424001']);
    [$mahasiswaB] = akunMahasiswa(['nim' => '531424002']);
    $mkA = matkul(['kode' => 'MKA']);
    $mkB = matkul(['kode' => 'MKB']);
    isiKrs($mahasiswaA, [$mkA]);
    isiKrs($mahasiswaB, [$mkB]);

    $this->actingAs($akunA)->get('/mahasiswa/krs')
        ->assertOk()
        ->assertSee($mahasiswaA->nim)
        ->assertDontSee($mahasiswaB->nim);

    // KRS milik A tidak pernah memuat mata kuliah milik KRS B.
    $krsA = Krs::where('mahasiswa_id', $mahasiswaA->id)->firstOrFail();
    expect($krsA->mataKuliahs()->pluck('mata_kuliahs.id')->all())->toBe([$mkA->id]);
});

test('mahasiswa bisa menambahkan mata kuliah', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    $mk = matkul();

    $this->actingAs($akun)->post('/mahasiswa/krs/mata-kuliah', [
        'mata_kuliah_id' => $mk->id,
    ])->assertRedirect(route('mahasiswa.krs.index'));

    $krs = Krs::where('mahasiswa_id', $mahasiswa->id)->firstOrFail();
    expect($krs->mataKuliahs()->count())->toBe(1)
        ->and($krs->totalSks())->toBe($mk->sks);
});

test('mata kuliah yang sama tidak boleh ditambahkan dua kali', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    $mk = matkul();
    $krs = isiKrs($mahasiswa, [$mk]);

    $this->actingAs($akun)->post('/mahasiswa/krs/mata-kuliah', [
        'mata_kuliah_id' => $mk->id,
    ])->assertSessionHasErrors('mata_kuliah_id');

    expect($krs->mataKuliahs()->count())->toBe(1);
});

test('unique constraint pivot mencegah duplikasi di level database', function () {
    [$mahasiswa] = akunMahasiswa();
    $mk = matkul();
    $krs = isiKrs($mahasiswa, [$mk]);

    expect(fn () => $krs->mataKuliahs()->attach($mk->id))
        ->toThrow(QueryException::class);
});

test('mahasiswa bisa menghapus mata kuliah dari KRS', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    $mk = matkul();
    $krs = isiKrs($mahasiswa, [$mk]);

    $this->actingAs($akun)->delete("/mahasiswa/krs/mata-kuliah/{$mk->id}")
        ->assertRedirect(route('mahasiswa.krs.index'));

    expect($krs->mataKuliahs()->count())->toBe(0);
});

test('total SKS dihitung ulang setiap ada perubahan', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    $a = matkul(['sks' => 3]);
    $b = matkul(['sks' => 4]);
    $krs = isiKrs($mahasiswa, []);

    $this->actingAs($akun)->post('/mahasiswa/krs/mata-kuliah', ['mata_kuliah_id' => $a->id]);
    expect($krs->refresh()->totalSks())->toBe(3);
    $this->actingAs($akun)->get('/mahasiswa/krs')->assertSee('3');

    $this->actingAs($akun)->post('/mahasiswa/krs/mata-kuliah', ['mata_kuliah_id' => $b->id]);
    expect($krs->refresh()->totalSks())->toBe(7);
    $this->actingAs($akun)->get('/mahasiswa/krs')->assertSee('Total SKS');

    $this->actingAs($akun)->delete("/mahasiswa/krs/mata-kuliah/{$a->id}");
    expect($krs->refresh()->totalSks())->toBe(4);

    $this->actingAs($akun)->delete("/mahasiswa/krs/mata-kuliah/{$b->id}");
    expect($krs->refresh()->totalSks())->toBe(0);
});

test('KRS diajukan terkunci dan tidak bisa diubah', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    $mk = matkul();
    $krs = isiKrs($mahasiswa, [$mk]);

    $this->actingAs($akun)->post('/mahasiswa/krs/ajukan')
        ->assertRedirect(route('mahasiswa.krs.index'));

    $krs->refresh();
    expect($krs->status)->toBe(KrsStatus::MenungguPersetujuan)
        ->and($krs->diajukan_pada)->not->toBeNull();

    // Isi KRS terkunci: tambah, hapus, dan ajukan ulang ditolak.
    $mk2 = matkul();
    $this->actingAs($akun)->post('/mahasiswa/krs/mata-kuliah', ['mata_kuliah_id' => $mk2->id])
        ->assertSessionHasErrors('krs');

    $this->actingAs($akun)->delete("/mahasiswa/krs/mata-kuliah/{$mk->id}")
        ->assertSessionHasErrors('krs');

    $this->actingAs($akun)->post('/mahasiswa/krs/ajukan')
        ->assertSessionHasErrors('krs');

    expect($krs->refresh()->status)->toBe(KrsStatus::MenungguPersetujuan)
        ->and($krs->mataKuliahs()->count())->toBe(1);
});

test('KRS final tidak bisa diajukan ulang', function (KrsStatus $status) {
    [$mahasiswa, $akun] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, [matkul()], $status);

    $this->actingAs($akun)->post('/mahasiswa/krs/ajukan')
        ->assertSessionHasErrors('krs');

    $mk = matkul();
    $this->actingAs($akun)->post('/mahasiswa/krs/mata-kuliah', ['mata_kuliah_id' => $mk->id])
        ->assertSessionHasErrors('krs');

    $krs->refresh();
    expect($krs->status)->toBe($status)
        ->and($krs->mataKuliahs()->count())->toBe(1);
})->with([KrsStatus::Disetujui, KrsStatus::Ditolak]);

test('halaman KRS mode baca untuk status non-draft', function () {
    [$mahasiswa, $akun] = akunMahasiswa();
    isiKrs($mahasiswa, [matkul()], KrsStatus::Disetujui);

    $this->actingAs($akun)->get('/mahasiswa/krs')
        ->assertOk()
        ->assertDontSee('Ajukan KRS')
        ->assertDontSee('Tambah Mata Kuliah');
});
