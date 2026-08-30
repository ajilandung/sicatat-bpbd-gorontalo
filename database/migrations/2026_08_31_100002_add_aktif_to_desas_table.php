<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan penanda aktif pada desa/kelurahan (FR-06).
 *
 * Desa yang salah atau sudah tidak dipakai tidak dihapus, melainkan
 * dinonaktifkan — sama seperti pola akun pengguna pada Fase 1. Alasannya:
 * desa yang sudah tercatat pada kegiatan penyaluran tidak boleh hilang,
 * karena laporan lama akan kehilangan nama wilayahnya. Desa nonaktif tetap
 * utuh di riwayat, hanya tidak lagi ditawarkan pada form input.
 *
 * Kabupaten dan kecamatan tidak diberi kolom ini karena datanya berasal dari
 * sumber resmi dan tidak dapat diubah lewat aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desas', function (Blueprint $table) {
            $table->boolean('aktif')->default(true)->after('jenis');

            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::table('desas', function (Blueprint $table) {
            $table->dropIndex(['aktif']);
            $table->dropColumn('aktif');
        });
    }
};
