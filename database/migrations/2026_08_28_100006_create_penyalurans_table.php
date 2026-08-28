<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyalurans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');

            // Pengguna yang menginput (PRD 8.5 - Informasi Sistem).
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // Angka di bawah ini berlaku untuk seluruh desa pada kegiatan ini.
            // Laporan lapangan memang sering menyebut satu angka gabungan untuk
            // beberapa desa sekaligus, mis. 12 Agustus 2026: empat desa, 16.000 liter.
            // KK dan jiwa boleh kosong karena kerap tidak tercatat di lapangan.
            $table->unsignedInteger('jumlah_kk')->nullable();
            $table->unsignedInteger('jumlah_jiwa')->nullable();
            $table->unsignedInteger('volume_liter');

            $table->text('keterangan')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyalurans');
    }
};
