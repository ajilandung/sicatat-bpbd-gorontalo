<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FotoPenyaluranController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Pengguna\PenggunaController;
use App\Http\Controllers\Pengguna\ResetPasswordController;
use App\Http\Controllers\PenyaluranController;
use App\Http\Controllers\Wilayah\DesaController;
use App\Http\Controllers\Wilayah\KabupatenController;
use App\Http\Controllers\Wilayah\KecamatanController;
use App\Http\Controllers\WilayahOptionController;
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

        // Modul Penyaluran (FR-08 sampai FR-18). Daftar dan detail terbuka
        // untuk seluruh role; menambah, mengubah, menghapus, dan memulihkan
        // hanya untuk admin. Route yang memakai kata tetap seperti `create`,
        // `terhapus`, dan `pulihkan` didaftarkan lebih dulu agar tidak
        // tertangkap sebagai id oleh route detail `{penyaluran}`.
        Route::prefix('penyaluran')->name('penyaluran.')->group(function () {
            Route::get('/', [PenyaluranController::class, 'index'])->name('index');

            // Dokumentasi foto kegiatan. Foto disimpan di luar folder publik,
            // jadi berkasnya hanya dapat dibuka lewat route ini — terbuka untuk
            // seluruh role yang login, sama seperti halaman detailnya.
            Route::get('foto/{foto}', [FotoPenyaluranController::class, 'tampil'])->name('foto.tampil');

            // Menginput dan mengoreksi kegiatan terbuka untuk admin dan petugas.
            // Middleware hanya menyaring role; batas kepemilikannya — petugas
            // hanya boleh mengoreksi kegiatan yang ia input sendiri — ditegakkan
            // `PenyaluranPolicy` di controller dan FormRequest, sehingga
            // mengetik URL milik orang lain tetap ditolak.
            Route::middleware('role:admin,petugas')->group(function () {
                Route::get('create', [PenyaluranController::class, 'create'])->name('create');
                Route::post('/', [PenyaluranController::class, 'store'])->name('store');

                Route::get('{penyaluran}/edit', [PenyaluranController::class, 'edit'])->name('edit');
                Route::put('{penyaluran}', [PenyaluranController::class, 'update'])->name('update');

                Route::post('{penyaluran}/foto', [FotoPenyaluranController::class, 'store'])->name('foto.store');
                Route::delete('foto/{foto}', [FotoPenyaluranController::class, 'destroy'])->name('foto.destroy');
            });

            // Menghapus dan memulihkan tetap khusus admin, termasuk untuk data
            // yang diinput petugas.
            Route::middleware('role:admin')->group(function () {
                Route::get('terhapus', [PenyaluranController::class, 'terhapus'])->name('terhapus');

                Route::delete('{penyaluran}', [PenyaluranController::class, 'destroy'])->name('destroy');
                Route::patch('{penyaluran}/pulihkan', [PenyaluranController::class, 'pulihkan'])->name('pulihkan');
            });

            // `withTrashed` supaya admin masih bisa memeriksa isi data yang
            // terhapus sebelum memutuskan memulihkannya. Role lain ditolak
            // di controller.
            Route::get('{penyaluran}', [PenyaluranController::class, 'show'])->name('show')->withTrashed();
        });

        // Laporan dan export (FR-22, FR-23, FR-24). Terbuka untuk admin dan
        // pimpinan; petugas pada MVP berperan sebagai sumber data lapangan
        // sehingga tidak diberi akses laporan (§9).
        Route::prefix('laporan')->name('laporan.')->middleware('role:admin,pimpinan')->group(function () {
            Route::get('/', [LaporanController::class, 'index'])->name('index');
            Route::get('cetak', [LaporanController::class, 'cetak'])->name('cetak');
            Route::get('excel', [LaporanController::class, 'excel'])->name('excel');
        });

        // Dropdown wilayah bertingkat (§7). Dipakai form input dan panel
        // filter, jadi terbuka untuk seluruh role yang sudah login.
        Route::prefix('options')->name('options.')->group(function () {
            Route::get('kecamatan', [WilayahOptionController::class, 'kecamatan'])->name('kecamatan');
            Route::get('desa', [WilayahOptionController::class, 'desa'])->name('desa');
        });

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

    });
});
