<?php

use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DosenPaController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\MataKuliahController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Dosen\ApprovalController;
use App\Http\Controllers\Mahasiswa\KrsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->role->homeRoute())
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('akun', AkunController::class)->except(['show']);
        Route::resource('mahasiswa', MahasiswaController::class)->except(['show']);
        Route::resource('dosen', DosenPaController::class)->except(['show'])->parameters(['dosen' => 'dosen']);
        Route::resource('matakuliah', MataKuliahController::class)
            ->except(['show'])
            ->parameters(['matakuliah' => 'mataKuliah']);
    });

/*
|--------------------------------------------------------------------------
| Mahasiswa
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:mahasiswa'])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {
        Route::get('/krs', [KrsController::class, 'index'])->name('krs.index');
        Route::post('/krs/mata-kuliah', [KrsController::class, 'store'])->name('krs.store');
        Route::delete('/krs/mata-kuliah/{mataKuliah}', [KrsController::class, 'destroy'])->name('krs.destroy');
        Route::post('/krs/ajukan', [KrsController::class, 'ajukan'])->name('krs.ajukan');
    });

/*
|--------------------------------------------------------------------------
| Dosen PA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:dosen_pa'])
    ->prefix('dosen-pa')
    ->name('dosen.')
    ->group(function () {
        Route::get('/krs-menunggu', [ApprovalController::class, 'index'])->name('pending.index');
        Route::get('/krs-menunggu/{krs}', [ApprovalController::class, 'show'])->name('krs.show');
        Route::post('/krs-menunggu/{krs}/setujui', [ApprovalController::class, 'setujui'])->name('krs.setujui');
        Route::post('/krs-menunggu/{krs}/tolak', [ApprovalController::class, 'tolak'])->name('krs.tolak');
    });
