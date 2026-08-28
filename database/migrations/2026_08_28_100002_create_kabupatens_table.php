<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kabupatens', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->nullable()->unique();
            $table->string('nama', 100);
            $table->enum('jenis', ['kabupaten', 'kota'])->default('kabupaten');
            $table->timestamps();

            // Nama saja tidak cukup: ada Kabupaten Gorontalo dan Kota Gorontalo.
            $table->unique(['nama', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kabupatens');
    }
};
