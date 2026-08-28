<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Instansi pelaksana pada satu kegiatan penyaluran. Satu kegiatan kerap
 * dikerjakan beberapa instansi sekaligus, mis. 3 Agustus 2026:
 * BPBD Provinsi Gorontalo, Polsek Bone Pantai, dan PDAM Bone Bolango.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instansi_penyaluran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyaluran_id')->constrained('penyalurans')->cascadeOnDelete();
            $table->foreignId('instansi_id')->constrained('instansis')->restrictOnDelete();

            $table->unique(['penyaluran_id', 'instansi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instansi_penyaluran');
    }
};
