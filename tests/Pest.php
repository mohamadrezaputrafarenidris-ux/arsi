<?php

use App\Enums\KrsStatus;
use App\Enums\Role;
use App\Models\DosenPa;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, it is "PHPUnit\Framework\TestCase". Of course, you may
| need to change the "pest()" function to bind a different class.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you
| can use to assert different things. Obviously, you may extend the Expectation API.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some test code specific to your project
| that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you reduce the number of lines of code in your tests.
|
*/

/**
 * Buat mahasiswa beserta akun login-nya.
 */
function akunMahasiswa(array $mahasiswa = [], array $user = []): array
{
    $mahasiswa = Mahasiswa::factory()->create($mahasiswa);
    $akun = User::factory()->create([
        'role' => Role::Mahasiswa,
        'mahasiswa_id' => $mahasiswa->id,
        'password' => 'mhs123',
        ...$user,
    ]);

    return [$mahasiswa, $akun];
}

/**
 * Buat dosen PA beserta akun login-nya.
 */
function akunDosen(array $dosen = [], array $user = []): array
{
    $dosen = DosenPa::factory()->create($dosen);
    $akun = User::factory()->create([
        'role' => Role::DosenPa,
        'dosen_pa_id' => $dosen->id,
        'password' => 'dosen123',
        ...$user,
    ]);

    return [$dosen, $akun];
}

/**
 * Akun admin.
 */
function akunAdmin(array $attributes = []): User
{
    return User::factory()->create([
        'role' => Role::Admin,
        'password' => 'admin123',
        ...$attributes,
    ]);
}

/**
 * Buat mata kuliah dengan jadwal tertentu.
 */
function matkul(array $attributes = []): MataKuliah
{
    return MataKuliah::factory()->create($attributes);
}

/**
 * Isi KRS milik mahasiswa tertentu dengan daftar mata kuliah.
 */
function isiKrs(Mahasiswa $mahasiswa, array $mataKuliahs, KrsStatus $status = KrsStatus::Draft): Krs
{
    $krs = Krs::factory()->create([
        'mahasiswa_id' => $mahasiswa->id,
        'status' => $status,
        'diajukan_pada' => $status === KrsStatus::Draft ? null : now(),
    ]);

    if ($mataKuliahs !== []) {
        $krs->mataKuliahs()->attach(collect($mataKuliahs)->pluck('id'));
    }

    return $krs;
}
