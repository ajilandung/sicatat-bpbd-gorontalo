<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dokumentasi foto kegiatan penyaluran.
 *
 * Foto selalu menempel pada satu kegiatan penyaluran, tidak pernah berdiri
 * sendiri. Karena itu tabel ini sengaja TIDAK memiliki kolom tanggal: tanggal
 * dokumentasi selalu dibaca dari `penyalurans.tanggal_penyaluran` lewat relasi,
 * sehingga foto tidak mungkin punya tanggal yang berbeda dari kegiatannya —
 * termasuk ketika tanggal kegiatan dikoreksi belakangan.
 *
 * Kegiatan dihapus memakai soft delete, jadi foto ikut tersembunyi bersama
 * kegiatannya dan muncul kembali saat kegiatan dipulihkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_penyalurans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penyaluran_id')->constrained('penyalurans')->cascadeOnDelete();

            // Pengunggah. Akun tidak pernah dihapus, hanya dinonaktifkan,
            // sehingga namanya selalu dapat ditampilkan kembali.
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // Lokasi berkas pada disk `local` (storage/app/private), mis.
            // `dokumentasi/12/xxxx.jpg`. Berkas sengaja disimpan di luar
            // folder publik dan hanya disajikan lewat route yang menjaga login.
            $table->string('path');

            // Hanya waktu unggah yang bermakna: baris foto tidak pernah diubah.
            $table->timestamp('created_at')->nullable();

            $table->index(['penyaluran_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_penyalurans');
    }
};
