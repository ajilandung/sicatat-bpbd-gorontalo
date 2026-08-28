@echo off
REM Menjalankan Sicatat di http://localhost:8000
REM Klik dua kali berkas ini, lalu biarkan jendela terbuka selama sistem dipakai.

cd /d "%~dp0"

REM Pakai php dari PATH bila ada; kalau tidak, pakai php bawaan Laragon.
set "PHPBIN=php"
where php >nul 2>nul || set "PHPBIN=C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe"

if not exist "%PHPBIN%" if "%PHPBIN%" neq "php" (
    echo.
    echo PHP tidak ditemukan di: %PHPBIN%
    echo Sesuaikan baris PHPBIN di dalam berkas jalankan.bat ini.
    echo.
    pause
    exit /b 1
)

echo ================================================
echo   Sicatat - BPBD Provinsi Gorontalo
echo   Alamat : http://localhost:8000
echo   Berhenti: tekan Ctrl+C
echo ================================================
echo.

start "" http://localhost:8000
"%PHPBIN%" artisan serve --host=127.0.0.1 --port=8000

pause
