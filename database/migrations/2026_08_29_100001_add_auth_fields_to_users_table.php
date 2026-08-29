<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melengkapi tabel users untuk kebutuhan Fase 1 (FR-01, FR-02).
 *
 * - last_login_at   : kolom "Terakhir login" pada tabel Manajemen Pengguna.
 * - harus_ganti_password : penanda password sementara. Admin membuat akun dengan
 *   password sementara, lalu pengguna wajib menggantinya pada login pertama
 *   sehingga admin tidak pernah tahu password akhir milik pengguna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('harus_ganti_password')->default(false)->after('aktif');
            $table->timestamp('last_login_at')->nullable()->after('harus_ganti_password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['harus_ganti_password', 'last_login_at']);
        });
    }
};
