<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabupaten_id')->constrained('kabupatens')->restrictOnDelete();
            $table->string('kode', 10)->nullable()->unique();
            $table->string('nama', 100);
            $table->timestamps();

            // Nama kecamatan bisa sama di kabupaten berbeda, jadi keunikan dijaga per kabupaten.
            $table->unique(['kabupaten_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kecamatans');
    }
};
