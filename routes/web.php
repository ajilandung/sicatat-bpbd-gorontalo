<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\Pengguna\PenggunaController;
use App\Http\Controllers\Pengguna\ResetPasswordController;
use App\Http\Controllers\Wilayah\DesaController;
use App\Http\Controllers\Wilayah\KabupatenController;
use App\Http\Controllers\Wilayah\KecamatanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::middleware(['auth', 'aktif'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Tetap di luar penjagaan `ganti-password`, jika tidak pengguna yang wajib
    // mengganti password sementara tidak punya jalan keluar.
    Route::get('ubah-password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('ubah-password', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware('ganti-password')->group(function () {
        // Pintu masuk umum, mengalihkan ke dashboard sesuai role.
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('dashboard/admin', [DashboardController::class, 'admin'])
            ->middleware('role:admin')->name('dashboard.admin');
        Route::get('dashboard/petugas', [DashboardController::class, 'petugas'])
            ->middleware('role:petugas')->name('dashboard.petugas');
        Route::get('dashboard/pimpinan', [DashboardController::class, 'pimpinan'])
            ->middleware('role:pimpinan')->name('dashboard.pimpinan');

        // Manajemen Pengguna — khusus Admin (FR-02).
        Route::middleware('role:admin')->group(function () {
            Route::resource('pengguna', PenggunaController::class)->except('destroy');

            Route::patch('pengguna/{pengguna}/status', [PenggunaController::class, 'ubahStatus'])
                ->name('pengguna.status');

            Route::get('pengguna/{pengguna}/reset-password', [ResetPasswordController::class, 'edit'])
                ->name('pengguna.reset-password');
            Route::put('pengguna/{pengguna}/reset-password', [ResetPasswordController::class, 'update'])
                ->name('pengguna.reset-password.update');

            // Master Data Wilayah (FR-04, FR-05, FR-06).
            // Kabupaten dan kecamatan hanya dapat dilihat: datanya berasal dari
            // sumber resmi. Desa/kelurahan dapat ditambah dan diubah, tetapi
            // tidak dapat dihapus — hanya dinonaktifkan.
            Route::prefix('wilayah')->name('wilayah.')->group(function () {
                Route::get('kabupaten', [KabupatenController::class, 'index'])->name('kabupaten.index');
                Route::get('kecamatan', [KecamatanController::class, 'index'])->name('kecamatan.index');

                Route::resource('desa', DesaController::class)->except(['show', 'destroy']);

                Route::patch('desa/{desa}/status', [DesaController::class, 'ubahStatus'])
                    ->name('desa.status');
            });

            // Master Data Instansi Pelaksana (FR-07).
            Route::resource('instansi', InstansiController::class)->except(['show', 'destroy']);

            Route::patch('instansi/{instansi}/status', [InstansiController::class, 'ubahStatus'])
                ->name('instansi.status');
        });

        // Menu Penyaluran menyusul pada Fase 3.
    });
});
