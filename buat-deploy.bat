@echo off
REM Membuat sicatat-deploy.zip yang siap diunggah ke Hostinger.
REM Klik dua kali berkas ini setiap kali tampilan atau kode berubah.

cd /d "%~dp0"

REM Pakai php dari PATH bila ada; kalau tidak, pakai php bawaan Laragon.
set "PHPBIN=php"
where php >nul 2>nul || set "PHPBIN=C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe"

if not exist "%PHPBIN%" if "%PHPBIN%" neq "php" (
    echo.
    echo PHP tidak ditemukan di: %PHPBIN%
    echo Sesuaikan baris PHPBIN di dalam berkas buat-deploy.bat ini.
    echo.
    pause
    exit /b 1
)

echo ================================================
echo   Sicatat - menyiapkan berkas untuk Hostinger
echo ================================================
echo.

REM Bangun ulang CSS dan JavaScript lebih dulu supaya zip berisi tampilan
REM terbaru. Dilewati bila npm tidak terpasang di komputer ini.
where npm >nul 2>nul && (
    echo [1/2] Membangun ulang tampilan ^(npm run build^)...
    call npm run build || (
        echo.
        echo Gagal membangun tampilan. Zip tidak dibuat.
        pause
        exit /b 1
    )
) || echo [1/2] npm tidak ditemukan, memakai aset di public\build apa adanya.

echo.
echo [2/2] Mengemas berkas...
"%PHPBIN%" buat-deploy.php || (
    pause
    exit /b 1
)

echo.
echo Selesai. Unggah sicatat-deploy.zip ke Hostinger, lalu ekstrak di sana.
echo.
pause
