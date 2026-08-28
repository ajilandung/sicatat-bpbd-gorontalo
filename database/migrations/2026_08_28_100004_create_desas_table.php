<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->restrictOnDelete();
            $table->string('kode', 15)->nullable()->unique();
            $table->string('nama', 100);
            $table->enum('jenis', ['desa', 'kelurahan'])->default('desa');
            $table->timestamps();

            $table->unique(['kecamatan_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desas');
    }
};
