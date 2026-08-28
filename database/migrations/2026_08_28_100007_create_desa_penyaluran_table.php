<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Desa penerima pada satu kegiatan penyaluran. Satu kegiatan dapat
 * mencakup beberapa desa sekaligus, sesuai cara pencatatan di lapangan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desa_penyaluran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyaluran_id')->constrained('penyalurans')->cascadeOnDelete();
            $table->foreignId('desa_id')->constrained('desas')->restrictOnDelete();

            // Satu desa tidak boleh tercatat dua kali pada kegiatan yang sama.
            $table->unique(['penyaluran_id', 'desa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa_penyaluran');
    }
};
