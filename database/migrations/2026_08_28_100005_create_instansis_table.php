<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instansis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150)->unique();
            $table->string('singkatan', 50)->nullable();
            $table->string('alamat')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instansis');
    }
};
