<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mengganti nama kolom `tanggal` menjadi `tanggal_penyaluran`.
 *
 * Nama baru menegaskan aturan bisnis BPBD: kolom ini adalah tanggal kegiatan
 * benar-benar terjadi di lapangan, bukan tanggal data dimasukkan ke sistem
 * (yang dicatat `created_at`). Laporan lapangan sering baru sampai ke admin
 * beberapa hari kemudian, sehingga keduanya tidak boleh disamakan.
 *
 * Migrasi pembuat tabel sudah memakai nama baru, jadi basis data yang dibangun
 * dari nol tidak perlu apa-apa lagi — karena itu perubahan di sini dijaga
 * pemeriksaan kolom, dan hanya berjalan pada basis data yang terlanjur migrasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('penyalurans', 'tanggal')) {
            return;
        }

        Schema::table('penyalurans', function (Blueprint $table) {
            $table->renameColumn('tanggal', 'tanggal_penyaluran');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('penyalurans', 'tanggal_penyaluran')) {
            return;
        }

        Schema::table('penyalurans', function (Blueprint $table) {
            $table->renameColumn('tanggal_penyaluran', 'tanggal');
        });
    }
};
