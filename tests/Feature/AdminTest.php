<?php

use App\Enums\KrsStatus;
use App\Enums\Role;
use App\Models\DosenPa;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin bisa membuat akun mahasiswa yang dikaitkan ke data', function () {
    $admin = akunAdmin();
    $mahasiswa = Mahasiswa::factory()->create();

    $this->actingAs($admin)->post('/admin/akun', [
        'username' => 'mhsbaru',
        'password' => 'rahasia123',
        'role' => Role::Mahasiswa->value,
        'mahasiswa_id' => $mahasiswa->id,
    ])->assertRedirect(route('admin.akun.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'mhsbaru',
        'role' => Role::Mahasiswa->value,
        'mahasiswa_id' => $mahasiswa->id,
    ]);
});

test('username akun harus unik', function () {
    $admin = akunAdmin();
    akunAdmin(['username' => 'sudahada']);

    $this->actingAs($admin)->post('/admin/akun', [
        'username' => 'sudahada',
        'password' => 'rahasia123',
        'role' => Role::Admin->value,
    ])->assertSessionHasErrors('username');
});

test('satu data mahasiswa hanya boleh punya satu akun', function () {
    $admin = akunAdmin();
    [$mahasiswa] = akunMahasiswa();

    $this->actingAs($admin)->post('/admin/akun', [
        'username' => 'mhsduplikat',
        'password' => 'rahasia123',
        'role' => Role::Mahasiswa->value,
        'mahasiswa_id' => $mahasiswa->id,
    ])->assertSessionHasErrors('mahasiswa_id');
});

test('role mahasiswa wajib memilih data mahasiswa', function () {
    $admin = akunAdmin();

    $this->actingAs($admin)->post('/admin/akun', [
        'username' => 'tanpaData',
        'password' => 'rahasia123',
        'role' => Role::Mahasiswa->value,
    ])->assertSessionHasErrors('mahasiswa_id');
});

test('edit akun dengan password kosong tidak mengubah password', function () {
    $admin = akunAdmin();
    $target = User::factory()->create(['username' => 'ubah', 'password' => 'awal12345']);

    $this->actingAs($admin)->put("/admin/akun/{$target->id}", [
        'username' => 'ubahbaru',
        'password' => '',
        'role' => Role::Admin->value,
    ])->assertRedirect(route('admin.akun.index'));

    $target->refresh();
    expect($target->username)->toBe('ubahbaru')
        ->and(Hash::check('awal12345', $target->password))->toBeTrue();
});

test('admin tidak boleh menghapus akunnya sendiri', function () {
    $admin = akunAdmin(['username' => 'admin']);

    $this->actingAs($admin)->delete("/admin/akun/{$admin->id}")
        ->assertRedirect();

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('menghapus akun tidak menghapus data mahasiswa', function () {
    $admin = akunAdmin();
    [$mahasiswa, $akun] = akunMahasiswa();

    $this->actingAs($admin)->delete("/admin/akun/{$akun->id}")
        ->assertRedirect(route('admin.akun.index'));

    $this->assertDatabaseMissing('users', ['id' => $akun->id]);
    $this->assertDatabaseHas('mahasiswas', ['id' => $mahasiswa->id]);
});

test('menghapus akun tidak menghapus data dosen pa', function () {
    $admin = akunAdmin();
    [$dosen, $akun] = akunDosen();

    $this->actingAs($admin)->delete("/admin/akun/{$akun->id}")
        ->assertRedirect(route('admin.akun.index'));

    $this->assertDatabaseMissing('users', ['id' => $akun->id]);
    $this->assertDatabaseHas('dosen_pas', ['id' => $dosen->id]);
});

test('admin bisa mengelola data mahasiswa', function () {
    $admin = akunAdmin();

    $this->actingAs($admin)->post('/admin/mahasiswa', [
        'nim' => '531424999',
        'nama' => 'Uji Coba',
        'prodi' => 'Sistem Informasi',
        'semester' => 3,
    ])->assertRedirect(route('admin.mahasiswa.index'));

    $mahasiswa = Mahasiswa::where('nim', '531424999')->firstOrFail();

    $this->actingAs($admin)->put("/admin/mahasiswa/{$mahasiswa->id}", [
        'nim' => '531424999',
        'nama' => 'Uji Coba Diubah',
        'prodi' => 'Sistem Informasi',
        'semester' => 4,
    ])->assertRedirect(route('admin.mahasiswa.index'));

    expect($mahasiswa->refresh()->nama)->toBe('Uji Coba Diubah');
});

test('nim harus unik', function () {
    $admin = akunAdmin();
    Mahasiswa::factory()->create(['nim' => '531424001']);

    $this->actingAs($admin)->post('/admin/mahasiswa', [
        'nim' => '531424001',
        'nama' => 'Duplikat',
        'prodi' => 'Sistem Informasi',
        'semester' => 3,
    ])->assertSessionHasErrors('nim');
});

test('mahasiswa dengan KRS draft bisa dihapus dan KRS ikut terhapus', function () {
    $admin = akunAdmin();
    [$mahasiswa] = akunMahasiswa();
    $mk = matkul();
    $krs = isiKrs($mahasiswa, [$mk]);

    $this->actingAs($admin)->delete("/admin/mahasiswa/{$mahasiswa->id}")
        ->assertRedirect(route('admin.mahasiswa.index'));

    $this->assertDatabaseMissing('mahasiswas', ['id' => $mahasiswa->id]);
    $this->assertDatabaseMissing('krs', ['id' => $krs->id]);
    $this->assertDatabaseMissing('krs_mata_kuliah', ['krs_id' => $krs->id]);
});

test('mahasiswa dengan KRS diajukan tidak bisa dihapus', function (KrsStatus $status) {
    $admin = akunAdmin();
    [$mahasiswa] = akunMahasiswa();
    $krs = isiKrs($mahasiswa, [matkul()], $status);

    $this->actingAs($admin)->delete("/admin/mahasiswa/{$mahasiswa->id}")
        ->assertSessionHas('error');

    $this->assertDatabaseHas('mahasiswas', ['id' => $mahasiswa->id]);
    $this->assertDatabaseHas('krs', ['id' => $krs->id]);
})->with([
    KrsStatus::MenungguPersetujuan,
    KrsStatus::Disetujui,
    KrsStatus::Ditolak,
]);

test('admin bisa mengelola data dosen pa', function () {
    $admin = akunAdmin();

    $this->actingAs($admin)->post('/admin/dosen', [
        'nip' => '199001012020011001',
        'nama' => 'Dosen Baru, M.Kom',
    ])->assertRedirect(route('admin.dosen.index'));

    $this->assertDatabaseHas('dosen_pas', ['nip' => '199001012020011001']);
});

test('mata kuliah divalidasi: sks, hari, dan urutan jam', function () {
    $admin = akunAdmin();

    $this->actingAs($admin)->post('/admin/matakuliah', [
        'kode' => 'MK900', 'nama' => 'Tes', 'sks' => 9,
        'hari' => 'Minggu',
        'jam_mulai' => '10:00', 'jam_selesai' => '08:00',
        'ruang' => 'R. 100',
    ])->assertSessionHasErrors(['sks', 'hari', 'jam_selesai']);

    $this->actingAs($admin)->post('/admin/matakuliah', [
        'kode' => 'MK901', 'nama' => 'Tes', 'sks' => 6,
        'hari' => 'Sabtu',
        'jam_mulai' => '08:00', 'jam_selesai' => '10:00',
        'ruang' => 'R. 100',
    ])->assertRedirect(route('admin.matakuliah.index'));

    $this->assertDatabaseHas('mata_kuliahs', ['kode' => 'MK901']);
});

test('jam selesai tidak boleh sama dengan jam mulai', function () {
    $admin = akunAdmin();

    $this->actingAs($admin)->post('/admin/matakuliah', [
        'kode' => 'MK902', 'nama' => 'Tes', 'sks' => 3,
        'hari' => 'Senin',
        'jam_mulai' => '08:00', 'jam_selesai' => '08:00',
        'ruang' => 'R. 100',
    ])->assertSessionHasErrors('jam_selesai');
});

test('mata kuliah yang dipakai KRS tidak bisa dihapus', function () {
    $admin = akunAdmin();
    [$mahasiswa] = akunMahasiswa();
    $mk = matkul();
    isiKrs($mahasiswa, [$mk]);

    $this->actingAs($admin)->delete("/admin/matakuliah/{$mk->id}")
        ->assertSessionHas('error');

    $this->assertDatabaseHas('mata_kuliahs', ['id' => $mk->id]);
});

test('mata kuliah yang tidak dipakai bisa dihapus', function () {
    $admin = akunAdmin();
    $mk = matkul();

    $this->actingAs($admin)->delete("/admin/matakuliah/{$mk->id}")
        ->assertRedirect(route('admin.matakuliah.index'));

    $this->assertDatabaseMissing('mata_kuliahs', ['id' => $mk->id]);
});

test('dashboard admin menampilkan ringkasan data', function () {
    $admin = akunAdmin();
    Mahasiswa::factory()->count(2)->create();
    DosenPa::factory()->count(2)->create();
    MataKuliah::factory()->count(3)->create();

    $this->actingAs($admin)->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('2')
        ->assertSee('KRS per Status');
});
