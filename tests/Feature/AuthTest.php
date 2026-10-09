<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('login sukses mengarahkan ke dashboard sesuai role', function () {
    $admin = akunAdmin(['username' => 'admin']);

    $this->post('/login', ['username' => 'admin', 'password' => 'admin123'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('login mahasiswa diarahkan ke halaman KRS', function () {
    [, $akun] = akunMahasiswa(['nim' => '531424001']);

    $this->post('/login', ['username' => $akun->username, 'password' => 'mhs123'])
        ->assertRedirect(route('mahasiswa.krs.index'));
});

test('login dosen PA diarahkan ke daftar KRS menunggu', function () {
    [, $akun] = akunDosen();

    $this->post('/login', ['username' => $akun->username, 'password' => 'dosen123'])
        ->assertRedirect(route('dosen.pending.index'));
});

test('login gagal menampilkan pesan umum', function () {
    akunAdmin(['username' => 'admin']);

    $this->post('/login', ['username' => 'admin', 'password' => 'salah'])
        ->assertSessionHasErrors('username', 'Username atau password salah.');

    // Pesan tidak menyebut mana yang salah, dan tidak membocorkan keberadaan akun.
    $this->post('/login', ['username' => 'tidakada', 'password' => 'salah'])
        ->assertSessionHasErrors('username', 'Username atau password salah.');

    $this->assertGuest();
});

test('password disimpan dalam bentuk hash', function () {
    $admin = akunAdmin(['username' => 'admin']);

    expect($admin->password)
        ->not->toBe('admin123')
        ->and(Hash::check('admin123', $admin->password))->toBeTrue();
});

test('logout mengakhiri sesi', function () {
    akunAdmin(['username' => 'admin']);

    $this->post('/login', ['username' => 'admin', 'password' => 'admin123'])
        ->assertRedirect(route('admin.dashboard'));

    $this->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
});

test('halaman login menolak user yang sudah masuk', function () {
    akunAdmin(['username' => 'admin']);

    $this->post('/login', ['username' => 'admin', 'password' => 'admin123']);

    $this->get('/login')->assertRedirect('/');
});

test('akses tanpa login diarahkan ke halaman login', function () {
    $this->get('/admin/dashboard')->assertRedirect('/login');
    $this->get('/mahasiswa/krs')->assertRedirect('/login');
    $this->get('/dosen-pa/krs-menunggu')->assertRedirect('/login');
});

test('role admin tidak bisa membuka halaman mahasiswa dan dosen', function () {
    $admin = akunAdmin();

    $this->actingAs($admin)->get('/mahasiswa/krs')->assertForbidden();
    $this->actingAs($admin)->get('/dosen-pa/krs-menunggu')->assertForbidden();
});

test('role mahasiswa tidak bisa membuka halaman admin dan dosen', function () {
    [, $akun] = akunMahasiswa();

    $this->actingAs($akun)->get('/admin/dashboard')->assertForbidden();
    $this->actingAs($akun)->get('/admin/akun')->assertForbidden();
    $this->actingAs($akun)->get('/dosen-pa/krs-menunggu')->assertForbidden();
});

test('role dosen tidak bisa membuka halaman admin dan mahasiswa', function () {
    [, $akun] = akunDosen();

    $this->actingAs($akun)->get('/admin/dashboard')->assertForbidden();
    $this->actingAs($akun)->get('/mahasiswa/krs')->assertForbidden();
});

test('tidak ada registrasi mandiri', function () {
    $this->post('/register', ['username' => 'baru', 'password' => 'rahasia123'])
        ->assertNotFound();

    expect(User::count())->toBe(0);
});
