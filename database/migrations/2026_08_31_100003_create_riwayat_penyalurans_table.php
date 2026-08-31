<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat perubahan data penyaluran (Technical Architecture §9.3).
 *
 * Data penyaluran boleh dikoreksi kapan saja karena laporan lapangan kerap
 * baru sampai ke admin beberapa hari setelah kegiatan berlangsung. Supaya
 * koreksi seperti itu tetap dapat ditelusuri, setiap perubahan dicatat di
 * sini: siapa yang mengubah, kapan, dan nilai sebelum/sesudahnya.
 *
 * Kolom `user_id` pada `penyalurans` hanya menyimpan penginput pertama,
 * sehingga tidak cukup untuk menjawab "siapa yang mengubah angka ini".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_penyalurans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penyaluran_id')->constrained('penyalurans')->cascadeOnDelete();

            // Pelaku perubahan. Akun tidak pernah dihapus, hanya dinonaktifkan,
            // sehingga nama pelaku selalu dapat ditampilkan kembali.
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->string('aksi', 20);

            // Nilai sebelum dan sesudah per kolom yang berubah, mis.
            // {"volume_liter": {"dari": 4000, "ke": 6000}}. Kosong untuk aksi
            // yang tidak mengubah isi data, seperti dihapus dan dipulihkan.
            $table->json('perubahan')->nullable();

            // Hanya waktu pembuatan yang bermakna: baris riwayat tidak pernah
            // diubah setelah tercatat.
            $table->timestamp('created_at')->nullable();

            $table->index(['penyaluran_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_penyalurans');
    }
};
