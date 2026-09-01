<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Nama hari dan bulan berbahasa Indonesia untuk `translatedFormat()`,
        // dipakai tampilan tanggal di seluruh halaman dan terutama pada
        // laporan cetak yang berbunyi "SENIN, 31 AGUSTUS 2026".
        Carbon::setLocale('id');
    }
}
