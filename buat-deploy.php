<?php

/*
 * Mengemas seluruh proyek menjadi sicatat-deploy.zip yang siap diunggah ke
 * Hostinger. Dijalankan lewat buat-deploy.bat, atau langsung dengan
 * `php buat-deploy.php`.
 *
 * Yang ikut: kode aplikasi, vendor/, public/build/, kedua .htaccess, dan
 * kerangka folder storage/ yang kosong. Yang sengaja ditinggal: .env asli
 * (berisi kata sandi), node_modules/, tests/, docs/, riwayat git, foto
 * dokumentasi lapangan, serta seluruh berkas cache dan log lokal.
 */

$akar = __DIR__;
$keluaran = $akar.DIRECTORY_SEPARATOR.'sicatat-deploy.zip';

$lewatiFolder = ['.git', 'node_modules', 'tests', 'docs', 'testing', '.idea', '.vscode'];
$lewatiBerkas = ['.env', '.env.backup', '.phpunit.result.cache', 'phpunit.xml',
    'jalankan.bat', 'buat-deploy.bat', 'buat-deploy.php', 'build.zip',
    'sicatat-deploy.zip', 'package-lock.json', 'AGENTS.md', 'CLAUDE.md'];

// Folder yang tetap harus ada di peladen, tetapi isinya tidak boleh ikut:
// cache dan sesi milik komputer lokal, serta foto dokumentasi kegiatan.
$kosongkan = ['storage/logs', 'bootstrap/cache', 'storage/framework/cache/data',
    'storage/framework/sessions', 'storage/framework/views',
    'storage/app/private/dokumentasi'];

if (! extension_loaded('zip')) {
    exit("Ekstensi zip PHP tidak aktif. Aktifkan extension=zip di php.ini.\n");
}

if (! is_file($akar.'/public/build/manifest.json')) {
    exit("public/build belum ada. Jalankan `npm run build` lebih dulu.\n");
}

$zip = new ZipArchive;
if ($zip->open($keluaran, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    exit("Gagal membuat $keluaran\n");
}

$jumlah = 0;
$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($akar, FilesystemIterator::SKIP_DOTS),
        fn ($berkas) => ! ($berkas->isDir() && in_array($berkas->getFilename(), $lewatiFolder, true))
    ),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $berkas) {
    $relatif = str_replace('\\', '/', substr($berkas->getPathname(), strlen($akar) + 1));

    $diKosongkan = false;
    foreach ($kosongkan as $folder) {
        if (str_starts_with($relatif, $folder.'/')) {
            $diKosongkan = true;
            break;
        }
    }

    if ($berkas->isDir()) {
        $zip->addEmptyDir($relatif);
    } elseif (! $diKosongkan && ! in_array($berkas->getFilename(), $lewatiBerkas, true)) {
        $zip->addFile($berkas->getPathname(), $relatif);
        $jumlah++;
    }
}

$zip->close();

printf("sicatat-deploy.zip siap: %d berkas, %.1f MB\n", $jumlah, filesize($keluaran) / 1048576);
