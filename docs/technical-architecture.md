# TECHNICAL ARCHITECTURE
## Sicatat — Sistem Informasi Pencatatan Penyaluran Bantuan Air Bersih

| | |
|---|---|
| **Instansi** | BPBD Provinsi Gorontalo |
| **Versi dokumen** | 1.0 |
| **Acuan** | [`PRD.md`](PRD.md) — MVP 1.0 |
| **Tahap** | Tahap 2 dari PRD bagian 14 |

---

## 1. Ringkasan Arsitektur

Sicatat adalah aplikasi web **monolitik server-rendered** berbasis Laravel. Tidak ada aplikasi frontend terpisah dan tidak ada API publik — seluruh halaman dirender di server memakai Blade, dengan sedikit JavaScript (Alpine.js) untuk interaksi lokal seperti dropdown wilayah bertingkat.

Pilihan ini diambil karena:

- **Skala kecil dan internal.** Pengguna hanya staf BPBD Provinsi Gorontalo, bukan publik. Beban server rendah.
- **Sederhana untuk dirawat.** Sesuai kebutuhan non-fungsional "Kemudahan Penggunaan" dan konteks proyek magang — satu codebase, satu proses deploy, tanpa build pipeline yang rumit.
- **Cepat dikembangkan.** Fitur MVP didominasi CRUD, filter, agregasi, dan export — semuanya adalah kekuatan bawaan Laravel.

```
┌─────────────┐   HTTPS    ┌──────────────────────────────────────────┐
│   Browser   │ ─────────► │            Laravel 13 (PHP 8.3)          │
│  (Chrome /  │            │                                          │
│   Edge)     │ ◄───────── │  Routes → Middleware → Controller        │
└─────────────┘   HTML     │            ↓                             │
      │                    │      Form Request (validasi)             │
      │ Alpine.js          │            ↓                             │
      │ (fetch JSON        │      Eloquent Model / Query Builder      │
      │  wilayah)          │            ↓                             │
      └──────────────────► │      Blade View / DomPDF / Excel         │
                           └────────────────┬─────────────────────────┘
                                            │ PDO
                                            ▼
                                   ┌──────────────────┐
                                   │   MySQL 8.4      │
                                   │   db: sicatat    │
                                   └──────────────────┘
```

---

## 2. Stack Teknologi

Versi berikut sudah diverifikasi terpasang di mesin pengembangan (Laragon).

| Lapisan | Teknologi | Versi | Keterangan |
|---|---|---|---|
| Bahasa | PHP | 8.3.33 | Ekstensi aktif: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `intl`, `zip`, `xml` |
| Framework | Laravel | 13.29 | Framework utama |
| Database | MySQL | 8.4.3 | Charset `utf8mb4`, collation `utf8mb4_unicode_ci` |
| Templating | Blade | bawaan Laravel | Seluruh halaman server-rendered |
| CSS | Tailwind CSS | 4.x | Bawaan skeleton Laravel, di-bundle Vite |
| Build tool | Vite | 8.x | `npm run dev` / `npm run build` |
| JS interaksi | Alpine.js | 3.x | Dropdown bertingkat, konfirmasi hapus, toggle sidebar |
| Grafik | Chart.js | 4.x | Grafik penyaluran per bulan (FR-21) |
| Export PDF | `barryvdh/laravel-dompdf` | 3.x | Render Blade → PDF (FR-23) |
| Export Excel | `maatwebsite/excel` | 3.x | Export `.xlsx` (FR-24) — lihat catatan §12 |
| Web server (dev) | `php artisan serve` / Laragon Apache | — | |
| Web server (prod) | Apache atau Nginx | — | Document root diarahkan ke `public/` |

**Yang sengaja TIDAK dipakai:**

- Livewire / Inertia / Vue / React — tidak diperlukan untuk cakupan MVP, menambah kompleksitas build.
- Laravel Breeze / starter kit — sudah bukan jalur resmi di Laravel 13, dan sistem ini tidak butuh registrasi publik. Autentikasi dibuat manual (lihat §5).
- Redis / queue worker — tidak ada pekerjaan asinkron di MVP. Export dijalankan sinkron.
- Paket permission pihak ketiga (Spatie) — hanya ada 3 role tetap, cukup dengan kolom `role` dan middleware sendiri.

---

## 3. Struktur Folder

```
D:\Sites\Sicatat\
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── Auth/PasswordController.php    ← ubah password sendiri
│   │   │   ├── DashboardController.php
│   │   │   ├── PenyaluranController.php
│   │   │   ├── Wilayah/KabupatenController.php
│   │   │   ├── Wilayah/KecamatanController.php
│   │   │   ├── Wilayah/DesaController.php
│   │   │   ├── InstansiController.php
│   │   │   ├── Pengguna/PenggunaController.php
│   │   │   ├── Pengguna/ResetPasswordController.php
│   │   │   ├── LaporanController.php
│   │   │   └── WilayahOptionController.php   ← endpoint JSON dropdown bertingkat
│   │   ├── Middleware/
│   │   │   ├── EnsureUserHasRole.php           ← alias `role:`
│   │   │   ├── PastikanAkunAktif.php           ← alias `aktif`
│   │   │   └── PastikanPasswordSudahDiganti.php ← alias `ganti-password`
│   │   └── Requests/                          ← validasi tiap form
│   ├── Policies/UserPolicy.php                ← aturan yang bergantung objek
│   ├── Models/
│   │   ├── User.php
│   │   ├── Kabupaten.php
│   │   ├── Kecamatan.php
│   │   ├── Desa.php
│   │   ├── Instansi.php
│   │   └── Penyaluran.php
│   ├── Exports/PenyaluranExport.php           ← Maatwebsite Excel
│   └── Support/RekapPenyaluran.php            ← kalkulasi ringkasan dashboard & laporan
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── UserSeeder.php
│       ├── WilayahSeeder.php
│       └── InstansiSeeder.php
├── docs/                                       ← dokumen proyek (folder ini)
├── resources/
│   ├── css/app.css
│   ├── js/app.js
│   └── views/
│       ├── layouts/app.blade.php               ← sidebar + topbar
│       ├── layouts/fokus.blade.php             ← layar tanpa navigasi (ganti password wajib)
│       ├── components/ui/                      ← input, pilihan, password, tombol, lencana, notifikasi, konfirmasi
│       ├── auth/{login,ubah-password}.blade.php
│       ├── dashboard/{admin,petugas,pimpinan}.blade.php
│       ├── penyaluran/{index,create,edit,show}.blade.php
│       ├── wilayah/{kabupaten,kecamatan,desa}/
│       ├── instansi/
│       ├── pengguna/{index,create,edit,show,reset-password}.blade.php
│       └── laporan/{index,pdf}.blade.php
└── routes/web.php
```

---

## 4. Alur Request

Contoh: Admin menyimpan data penyaluran baru.

```
POST /penyaluran
   ↓
middleware: web, auth, role:admin
   ↓
StorePenyaluranRequest        → validasi: tanggal_penyaluran wajib, boleh tanggal
                                 yang sudah lewat, tidak boleh di masa depan
                                 (before_or_equal:today), desa_id ada,
                                 jumlah_kk ≥ 0, volume_liter ≥ 1, dst.
   ↓
PenyaluranController@store    → set user_id = pengguna yang login
   ↓
Penyaluran::create()          → INSERT ke tabel penyalurans
   ↓
redirect ke /penyaluran dengan flash message "Data penyaluran berhasil disimpan."
```

---

## 5. Autentikasi & Otorisasi

### 5.1 Autentikasi (FR-01)

Dibuat manual, tanpa starter kit:

- Halaman `GET /login` menampilkan form.
- `POST /login` memakai `Auth::attempt()`. Sesuai PRD 8.1, pengguna boleh masuk dengan **email atau username** — controller mendeteksi apakah input mengandung `@`, lalu memilih kolom yang dipakai.
- Password disimpan sebagai hash bcrypt (bawaan Laravel), tidak pernah disimpan dalam bentuk asli.
- Rate limiting login: maksimal 5 percobaan gagal per menit per kombinasi IP + username.
- Sesi diregenerasi setelah login berhasil untuk mencegah session fixation.
- **Tidak ada halaman registrasi publik.** Akun hanya dibuat oleh Admin melalui menu Manajemen Pengguna (FR-03). Halaman login juga tidak menyediakan login pihak ketiga (Google, Facebook, dan sejenisnya).
- Akun nonaktif ditolak saat login. Bila akun dinonaktifkan ketika sesinya sedang berjalan, middleware `PastikanAkunAktif` memutus sesi itu pada permintaan berikutnya.
- Waktu login terakhir dicatat di `users.last_login_at` tanpa menyentuh `updated_at`, supaya jejak perubahan data pengguna tidak tertimpa aktivitas login.
- **Password sementara.** Akun yang baru dibuat dan akun yang direset admin ditandai `harus_ganti_password = true`. Middleware `PastikanPasswordSudahDiganti` mengunci pengguna di halaman `Ubah Password` — hanya halaman itu dan logout yang tetap terbuka — sampai password baru dibuat. Dengan begitu administrator tidak pernah mengetahui password akhir milik pengguna.
- **Tidak ada reset password lewat email pada MVP.** Halaman login mengarahkan pengguna yang lupa password untuk menghubungi administrator, yang kemudian memakai aksi *Reset Password* pada Manajemen Pengguna.

### 5.2 Otorisasi (FR-02)

Role disimpan sebagai kolom `role` di tabel `users` dengan tiga nilai tetap: `admin`, `petugas`, `pimpinan`.

Middleware `EnsureUserHasRole` (alias `role`) dipasang per grup route:

```php
Route::middleware(['auth'])->group(function () {
    // Semua role yang sudah login
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/penyaluran', [PenyaluranController::class, 'index']);
    Route::get('/penyaluran/{penyaluran}', [PenyaluranController::class, 'show']);
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::get('/laporan/pdf', [LaporanController::class, 'pdf']);
    Route::get('/laporan/excel', [LaporanController::class, 'excel']);

    // Hanya admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('penyaluran', PenyaluranController::class)->except(['index', 'show']);
        Route::resource('wilayah/kabupaten', KabupatenController::class);
        Route::resource('wilayah/kecamatan', KecamatanController::class);
        Route::resource('wilayah/desa', DesaController::class);
        Route::resource('instansi', InstansiController::class);
        Route::resource('pengguna', PenggunaController::class);
    });
});
```

### 5.3 Matriks Hak Akses

Diturunkan dari PRD bagian 9.

| Menu / Aksi | Admin | Pimpinan | Petugas |
|---|:---:|:---:|:---:|
| Login | ✅ | ✅ | ✅ |
| Dashboard sesuai role | ✅ | ✅ | ✅ |
| Ubah password sendiri | ✅ | ✅ | ✅ |
| Riwayat penyaluran (lihat & filter) | ✅ | ✅ | ✅ |
| Detail penyaluran | ✅ | ✅ | ✅ |
| Tambah / ubah / hapus penyaluran | ✅ | ❌ | ❌ |
| Laporan + export PDF & Excel | ✅ | ✅ | ✅ |
| Data wilayah (CRUD) | ✅ | ❌ | ❌ |
| Data instansi (CRUD) | ✅ | ❌ | ❌ |
| Manajemen pengguna (lihat, tambah, ubah) | ✅ | ❌ | ❌ |
| Aktifkan / nonaktifkan akun | ✅ | ❌ | ❌ |
| Reset password pengguna lain | ✅ | ❌ | ❌ |

Menu di sidebar disembunyikan bila role tidak berhak, **dan** tetap dijaga di sisi route/middleware — menyembunyikan tautan saja bukan pengamanan.

Sesuai PRD, role **Petugas** pada MVP belum punya kemampuan input; aksesnya sama dengan Pimpinan (baca saja). Kolom `role` sudah menyediakan tempatnya, sehingga menambah hak input di tahap berikutnya cukup mengubah middleware, bukan mengubah struktur data.

Setiap role punya halaman dashboard sendiri (`/dashboard/admin`, `/dashboard/petugas`, `/dashboard/pimpinan`) yang masing-masing dijaga middleware `role:`. URL `/dashboard` hanya pintu masuk yang mengalihkan pengguna ke dashboard miliknya.

Dua aturan otorisasi bergantung pada objek, bukan sekadar role, sehingga ditangani `UserPolicy`: admin tidak dapat menonaktifkan, menurunkan role, maupun mereset password akunnya sendiri. Tanpa penjagaan itu, admin terakhir bisa mengunci dirinya di luar sistem.

---

## 6. Peta Route

| Method | URI | Controller | Role | FR |
|---|---|---|---|---|
| GET | `/` | redirect ke `/dashboard` atau `/login` | — | — |
| GET | `/login` | `Auth\LoginController@create` | tamu | FR-01 |
| POST | `/login` | `Auth\LoginController@store` | tamu | FR-01 |
| POST | `/logout` | `Auth\LoginController@destroy` | auth | FR-01 |
| GET | `/dashboard` | `DashboardController@index` — alihkan ke dashboard sesuai role | semua | FR-02 |
| GET | `/dashboard/admin` | `DashboardController@admin` | admin | FR-19, 20, 21 |
| GET | `/dashboard/petugas` | `DashboardController@petugas` | petugas | FR-19, 20, 21 |
| GET | `/dashboard/pimpinan` | `DashboardController@pimpinan` | pimpinan | FR-19, 20, 21 |
| GET | `/ubah-password` | `Auth\PasswordController@edit` | auth | FR-01 |
| PUT | `/ubah-password` | `Auth\PasswordController@update` | auth | FR-01 |
| GET | `/penyaluran` | `PenyaluranController@index` | semua | FR-15, 16, 17, 18 |
| GET | `/penyaluran/create` | `PenyaluranController@create` | admin | FR-08 |
| POST | `/penyaluran` | `PenyaluranController@store` | admin | FR-08, 11–14 |
| GET | `/penyaluran/terhapus` | `PenyaluranController@terhapus` — data yang sudah dihapus | admin | FR-10 |
| GET | `/penyaluran/{id}` | `PenyaluranController@show` — `withTrashed`, data terhapus hanya untuk admin | semua | FR-15 |
| GET | `/penyaluran/{id}/edit` | `PenyaluranController@edit` | admin | FR-09 |
| PUT | `/penyaluran/{id}` | `PenyaluranController@update` | admin | FR-09 |
| DELETE | `/penyaluran/{id}` | `PenyaluranController@destroy` — *soft delete* | admin | FR-10 |
| PATCH | `/penyaluran/{id}/pulihkan` | `PenyaluranController@pulihkan` | admin | FR-10 |
| GET | `/laporan` | `LaporanController@index` | semua | FR-22 |
| GET | `/laporan/pdf` | `LaporanController@pdf` | semua | FR-23 |
| GET | `/laporan/excel` | `LaporanController@excel` | semua | FR-24 |
| GET | `/wilayah/kabupaten` | `Wilayah\KabupatenController@index` — daftar saja | admin | FR-04 |
| GET | `/wilayah/kecamatan` | `Wilayah\KecamatanController@index` — daftar saja | admin | FR-05 |
| resource | `/wilayah/desa` (kecuali `show`, `destroy`) | `Wilayah\DesaController` | admin | FR-06 |
| PATCH | `/wilayah/desa/{id}/status` | `Wilayah\DesaController@ubahStatus` | admin | FR-06 |
| resource | `/instansi` (kecuali `show`, `destroy`) | `InstansiController` | admin | FR-07 |
| PATCH | `/instansi/{id}/status` | `InstansiController@ubahStatus` | admin | FR-07 |
| resource | `/pengguna` (kecuali `destroy`) | `Pengguna\PenggunaController` | admin | FR-03 |
| PATCH | `/pengguna/{id}/status` | `Pengguna\PenggunaController@ubahStatus` | admin | FR-03 |
| GET | `/pengguna/{id}/reset-password` | `Pengguna\ResetPasswordController@edit` | admin | FR-03 |
| PUT | `/pengguna/{id}/reset-password` | `Pengguna\ResetPasswordController@update` | admin | FR-03 |
| GET | `/options/kecamatan` | `WilayahOptionController@kecamatan` | auth | FR-17 |
| GET | `/options/desa` | `WilayahOptionController@desa` | auth | FR-17 |

---

## 7. Dropdown Wilayah Bertingkat (PRD 8.3)

Dua endpoint JSON internal dipakai oleh form input penyaluran dan panel filter. Keduanya berada di belakang middleware `auth` — bukan API publik.

```
GET /options/kecamatan?kabupaten_id=2   →  [{"id":14,"nama":"Pulubala"}, ...]
GET /options/desa?kecamatan_id=14       →  [{"id":231,"nama":"Molyonegoro"}, ...]
```

Di sisi Blade, Alpine.js menangani perubahan pilihan:

```html
<div x-data="wilayahPicker()">
    <select x-model="kabupatenId" @change="muatKecamatan()" name="kabupaten_id"> ... </select>

    <select x-model="kecamatanId" @change="muatDesa()" name="kecamatan_id" :disabled="!kabupatenId">
        <template x-for="k in kecamatans" :key="k.id">
            <option :value="k.id" x-text="k.nama"></option>
        </template>
    </select>

    <select x-model="desaId" name="desa_id" :disabled="!kecamatanId">
        <template x-for="d in desas" :key="d.id">
            <option :value="d.id" x-text="d.nama"></option>
        </template>
    </select>
</div>
```

Saat form **edit**, komponen diinisialisasi dengan `kabupaten_id` dan `kecamatan_id` milik data yang sedang dibuka, lalu langsung memuat kedua daftar agar pilihan lama tampil benar.

Pada **form penyaluran**, langkah ketiga bukan memilih satu desa melainkan **mencentang satu atau beberapa desa**, karena satu kegiatan dapat mencakup beberapa desa sekaligus. Desa yang sudah dicentang ditampilkan sebagai daftar terpilih, dan admin boleh berpindah kecamatan untuk menambah desa dari kecamatan lain tanpa kehilangan pilihan sebelumnya. Pada **panel filter**, langkah ketiga tetap pilihan tunggal.

Catatan: hanya id desa yang benar-benar disimpan (lewat tabel `desa_penyaluran`). Kabupaten dan kecamatan diturunkan lewat relasi, sehingga tidak mungkin terjadi data lokasi yang tidak konsisten. Ini penting karena **61 nama desa di Provinsi Gorontalo tidak unik** — memilih desa dari nama saja akan ambigu.

---

## 8. Dashboard (PRD 8.2)

Seluruh angka dihitung langsung lewat query agregasi, tanpa tabel ringkasan dan tanpa cache — jumlah baris pada skala kegiatan BPBD (ribuan per tahun) masih sangat ringan untuk MySQL.

| Kartu / Panel | Sumber Data | FR |
|---|---|---|
| Total kegiatan penyaluran | `COUNT(*)` dari `penyalurans` | — |
| Total volume air tersalur | `SUM(volume_liter)` | FR-19 |
| Jumlah wilayah penerima | `COUNT(DISTINCT desa_id)` lewat `desa_penyaluran` | FR-20 |
| Kegiatan bulan ini | `COUNT(*)` dengan filter bulan berjalan | — |
| Grafik penyaluran per bulan | `SUM(volume_liter)` di-`GROUP BY` bulan, 12 bulan terakhir | FR-21 |
| Wilayah paling sering menerima | `COUNT(*)` di-`GROUP BY desa_id` lewat `desa_penyaluran`, ambil 5 teratas | — |
| Data penyaluran terbaru | 5 baris terakhir urut `tanggal_penyaluran` menurun | — |
| Kelengkapan data | Berapa kegiatan yang belum mencantumkan KK atau jiwa | — |

Kartu terakhir ada karena `jumlah_kk` dan `jumlah_jiwa` boleh kosong: angka total tetap ditampilkan, tetapi disertai keterangan berapa data yang belum lengkap, sehingga pembaca tahu totalnya belum mencakup semua kegiatan.

Semua logika agregasi dikumpulkan di satu kelas `App\Support\RekapPenyaluran` agar dashboard dan laporan memakai perhitungan yang sama persis — tidak ada dua versi rumus yang bisa berbeda hasilnya.

---

## 9. Riwayat, Filter, dan Laporan

### 9.1 Filter (PRD 8.7)

Filter dikirim sebagai query string agar hasil pencarian bisa di-*bookmark* dan dibagikan:

```
/penyaluran?tanggal_mulai=2026-08-01&tanggal_akhir=2026-08-24
           &kabupaten_id=2&kecamatan_id=14&desa_id=&instansi_id=3&user_id=&q=
```

Rentang `tanggal_mulai` dan `tanggal_akhir` selalu dicocokkan ke kolom `tanggal_penyaluran`, **bukan** `created_at`. Dengan begitu kegiatan yang datanya baru masuk beberapa hari kemudian tetap muncul pada tanggal kejadiannya.

Diterapkan sebagai Eloquent *local scope* pada model `Penyaluran`, sehingga potongan kode yang sama dipakai ulang oleh halaman riwayat, halaman laporan, export PDF, dan export Excel. Filter kabupaten dan kecamatan bekerja lewat `whereHas` ke relasi desa.

### 9.2 Laporan & Export (PRD 8.8, 8.9)

Halaman laporan memakai **filter yang sama** dengan halaman riwayat, lalu menampilkan blok ringkasan (periode, total air, jumlah wilayah, total KK, total jiwa) di atas tabel rincian.

Kedua format export sengaja dibuat **berbeda bentuk**, karena kegunaannya memang berbeda.

- **PDF (FR-23)** — `barryvdh/laravel-dompdf` merender view `laporan/pdf.blade.php`. View ini terpisah dari view web dan memakai CSS sederhana, karena DomPDF tidak mendukung Tailwind/Flexbox modern. Susunannya **meniru dokumen infografis yang selama ini dibuat kantor**: dikelompokkan per tanggal → kabupaten → kecamatan → desa, dengan "Total tersalur" pada tiap tanggal, lalu ditutup blok "Ringkasan Keseluruhan" berisi total liter dan sebaran per kabupaten (jumlah kecamatan dan desa). Tanpa kop surat resmi dan tanpa blok tanda tangan (lihat §12.1).
- **Excel (FR-24)** — `maatwebsite/excel` mengekspor `.xlsx` berbentuk **tabel datar**, satu baris per kegiatan dengan kolom Tanggal, Kabupaten, Kecamatan, Desa, KK, Jiwa, Volume, Pelaksana, dan Penginput, ditambah baris total di bagian bawah. Bentuk ini siap diolah ulang dengan *pivot table*. Untuk kegiatan yang mencakup beberapa desa, nama-nama desanya digabung dalam satu sel dan diberi penanda bahwa angkanya adalah angka gabungan.

Kedua export **mengalirkan filter yang sedang aktif**, sehingga isi berkas selalu sama dengan yang terlihat di layar.

### 9.3 Data Susulan dan Koreksi Historis

Aturan bisnis BPBD: **data lapangan tidak selalu sampai ke admin pada hari kegiatan berlangsung.** Hari Rabu admin bisa menerima tiga kegiatan yang terjadi hari Rabu, lalu hari Kamis baru diketahui ada empat kegiatan lain yang juga terjadi hari Rabu — sehingga total kegiatan hari Rabu menjadi tujuh setelah data dilengkapi. Aturan ini mengikat seluruh modul dan tidak boleh disederhanakan.

| Aspek | Ketentuan |
|---|---|
| Kolom | `tanggal_penyaluran` = tanggal kegiatan terjadi. `created_at` = waktu data dimasukkan ke sistem. Tidak pernah disamakan |
| Input | Admin bebas memilih tanggal yang sudah lewat. Batas satu-satunya: tidak boleh melewati hari ini (`before_or_equal:today`), semata untuk menangkap salah ketik tahun |
| Koreksi | Admin dapat mengubah dan menghapus data historis kapan saja (FR-09, FR-10). Penghapusan memakai *soft delete* sehingga masih dapat dipulihkan |
| Rekap & dashboard | Seluruh pengelompokan, `COUNT`, dan `SUM` memakai `tanggal_penyaluran`. Data susulan otomatis masuk ke tanggal kejadiannya |
| Filter & laporan | Rentang tanggal dicocokkan ke `tanggal_penyaluran` |
| Export | Berkas PDF/Excel yang sudah diunduh adalah *snapshot* saat itu dan tidak ikut berubah. Export ulang untuk periode yang sama wajib memuat seluruh data terbaru |

**Riwayat perubahan.** Karena data historis boleh dikoreksi belakangan, setiap perubahan pada data penyaluran dicatat: siapa yang mengubah, kapan, dan nilai sebelum/sesudahnya. Ini melengkapi `user_id` yang hanya menyimpan penginput.

Diwujudkan sebagai tabel `riwayat_penyalurans` (Database Schema §3.9), bukan paket pihak ketiga, agar label aksinya berbahasa Indonesia dan bentuk datanya sepenuhnya terkendali. Empat aksi dicatat: `dibuat`, `diubah`, `dihapus`, dan `dipulihkan`. Hanya kolom yang benar-benar berubah yang disimpan, sehingga menyimpan tanpa mengubah apa pun tidak menambah baris riwayat. Panelnya tampil di halaman detail penyaluran dan **hanya terlihat oleh admin** — yang membacanya adalah pihak yang juga berwenang mengoreksi datanya.

**Penghapusan yang dapat dibatalkan.** `destroy` hanya menandai `deleted_at`, dan halaman **Data Terhapus** (`/penyaluran/terhapus`, khusus admin) menyediakan tombol Pulihkan. Tanpa halaman itu, janji "kesalahan hapus masih bisa dipulihkan" hanya berlaku bagi yang punya akses basis data.

**Peringatan kegiatan serupa.** Sebelum menyimpan, sistem memeriksa apakah sudah ada kegiatan lain pada tanggal yang sama yang menyentuh salah satu desa terpilih. Bila ada, form dikembalikan beserta daftar kegiatan tersebut dan admin diminta menegaskan lewat kotak konfirmasi. Duplikat tetap **tidak dilarang** — satu desa memang bisa menerima lebih dari satu kegiatan dalam sehari (§12.2 #2) — sistem hanya memastikan admin sudah melihatnya lebih dulu.

---

## 10. Keamanan (Kebutuhan Non-Fungsional PRD 11)

| Aspek | Penanganan |
|---|---|
| Autentikasi | Wajib login untuk seluruh halaman kecuali `/login` |
| Password | Hash bcrypt bawaan Laravel; aturan minimal 8 karakter |
| Brute force | `RateLimiter` 5 percobaan gagal per menit |
| Otorisasi | Middleware `role` per grup route, bukan sekadar menyembunyikan menu |
| CSRF | Token `@csrf` pada seluruh form (bawaan Laravel) |
| XSS | Blade meng-escape output secara default; `{!! !!}` tidak dipakai untuk data pengguna |
| SQL Injection | Seluruh query lewat Eloquent / query builder dengan parameter binding |
| Session | Regenerasi ID saat login, invalidasi saat logout |
| Kredensial | Kredensial database dan `APP_KEY` hanya di `.env`, tidak ikut ke repositori |
| Penghapusan data | `penyalurans` memakai *soft delete* — data yang dihapus admin masih bisa dipulihkan dari database bila terjadi kesalahan |

---

## 11. Antarmuka (Kebutuhan Non-Fungsional PRD 11)

### 11.1 Design System Visual

Warna didefinisikan sekali sebagai token Tailwind pada `resources/css/app.css` (blok `@theme`),
bukan ditulis berulang di tiap view. Menambah atau mengubah warna cukup dilakukan di satu berkas
itu, dan seluruh aplikasi ikut berubah.

| Token | Skala | Arti | Dipakai untuk |
|---|---|---|---|
| `air` | `50`–`900`, inti `#0ea5e9` | Biru — warna primer sekaligus aksen | Logo, tombol utama, tautan, penanda menu aktif, cincin fokus, garis aksen, panel informasi |
| `navy` | `50`–`950`, inti `#142639` | Navy — profesionalitas | Sidebar, panel identitas halaman login, judul dan teks utama, tint pembeda peran Admin |
| `slate` (bawaan) | — | Netral | Latar halaman, teks penunjang, garis pembatas; putih untuk permukaan kartu |

Warna status memakai palet bawaan Tailwind — `emerald` (berhasil), `amber` (peringatan),
`red` (galat) — supaya maknanya langsung terbaca tanpa menambah jumlah warna identitas.

Token `brand` (oranye BPBD) pernah menjadi warna aksen di sini dan **sudah dihapus seluruhnya**
atas permintaan pemilik proyek. Oranye tidak lagi dipakai sebagai warna antarmuka; yang tersisa
di aplikasi hanya oranye sebagai isi foto dan video dokumentasi kegiatan, bukan warna yang
dipilih oleh design system.

**Aturan pemakaian.** Biru dipakai sebagai aksen, bukan sebagai latar halaman: satu tombol
utama per layar, satu batang biru pada menu yang sedang aktif, satu garis aksen pada kartu
sambutan dan panel login. Sisanya dibiarkan putih, abu terang, dan navy. Ketika sebuah tint
harus dibedakan dari `air` — misalnya lencana dan avatar peran Admin di sebelah peran Pimpinan
yang memakai tint `air` — pembedanya diambil dari `navy`, bukan dari warna baru.

**Kontras (WCAG AA).** Teks putih di atas biru hanya dipakai mulai `air-700` (5,9:1);
`air-500`/`600` disediakan untuk elemen grafis, garis, dan cincin fokus yang cukup memenuhi
ambang 3:1. Tautan beraksen memakai `air-700` di atas latar terang. Di atas latar navy, aksen
dinaikkan ke `air-400`/`air-500` karena `air-600` ke atas terlalu dekat dengan navy dan
tenggelam. Sidebar `navy-900` dengan teks `navy-200` berada di atas 7:1. Menu aktif tidak
memakai blok biru penuh — kombinasi latar `navy-800`, batang `air-400` di tepi kiri, dan ikon
`air-400` memberi penanda yang jelas tanpa menurunkan kontras teks.

Komponen Blade di `resources/views/components/ui/` adalah tempat token itu diterapkan, sehingga
halaman baru tidak perlu menyalin kelas warna atau ukuran:

| Komponen | Kegunaan |
|---|---|
| `x-ui.kepala-halaman` | Judul halaman, deskripsi, tautan kembali, dan tombol aksi |
| `x-ui.kartu` | Permukaan putih dengan kepala, badan, dan kaki opsional |
| `x-ui.kartu-statistik` | Kartu angka pada dashboard |
| `x-ui.kolom` | Satu kolom formulir: label, kendali, petunjuk, pesan galat |
| `x-ui.input`, `x-ui.pilihan`, `x-ui.password` | Kendali formulir setinggi 44px dengan status galat |
| `x-ui.tombol`, `x-ui.tombol-ikon` | Tombol dengan varian dan ukuran tetap |
| `x-ui.notifikasi`, `x-ui.ringkasan-galat` | Pesan status dan ringkasan kesalahan validasi |
| `x-ui.lencana`, `x-ui.lencana-role`, `x-ui.avatar` | Penanda status, peran, dan identitas |
| `x-ui.konfirmasi`, `x-ui.kosong` | Dialog konfirmasi dan tampilan data kosong |

Aturan antarmuka lain yang dipegang: satu halaman punya satu `<h1>` di badan halaman (topbar
hanya menampilkan penanda posisi), tinggi kendali formulir dan tombol pada satu baris selalu
sama, setiap elemen yang dapat difokus punya cincin fokus yang terlihat, dan tabel yang lebar
diganti daftar kartu di bawah lebar `md`.

### 11.2 Responsif

Target: laptop, desktop, dan tablet.

- Layout utama: sidebar tetap pada layar ≥ `lg`, berubah menjadi menu geser (*drawer*) pada layar lebih kecil.
- Tabel riwayat dibungkus kontainer `overflow-x-auto` agar tidak merusak lebar halaman pada tablet.
- Form input memakai satu kolom pada layar sempit dan dua kolom pada layar lebar.
- Grafik Chart.js diatur `responsive: true` dengan rasio aspek tetap.

---

## 12. Keputusan

### 12.1 Sudah Ditetapkan

| # | Isu | Keputusan |
|---|---|---|
| 1 | **Arti field "Petugas"** — PRD 8.5 menyebut "petugas yang menginput", sedangkan filter 8.7 hanya menyebut "Petugas". | Dipakai sebagai **pengguna sistem yang menginput data**, terisi otomatis dari akun yang sedang login (`penyalurans.user_id`). Admin tidak perlu mengisi apa pun. Tidak ada tabel master petugas lapangan di MVP. |
| 2 | **Sumber data wilayah** | Diambil dari ekspor **PENTAGON** "Jumlah Penduduk Berdasarkan Jenis Kelamin" yang memuat daftar wilayah beserta kode resminya. Sudah diolah menjadi `database/data/wilayah-gorontalo.csv` dan dimuat `WilayahSeeder`: **6 kabupaten/kota, 77 kecamatan, 729 desa/kelurahan**. |
| 3 | **Satu kegiatan dapat mencakup beberapa desa** — laporan asli kerap menulis satu angka gabungan untuk beberapa desa sekaligus. | Relasi desa ↔ penyaluran dibuat **banyak-ke-banyak**. Angka KK, jiwa, dan volume berlaku untuk seluruh desa pada kegiatan tersebut, ditandai sebagai *angka gabungan*, dan dibagi rata bila dibutuhkan rekap per desa. |
| 4 | **Satu kegiatan dapat dikerjakan beberapa instansi** — mis. BPBD Provinsi, Polsek, dan PDAM bersama-sama. | Relasi instansi ↔ penyaluran dibuat **banyak-ke-banyak**, dipilih dengan centang pada form. Filter dan rekap per instansi tetap berfungsi. |
| 5 | **Kelengkapan KK dan jiwa** — banyak entri laporan hanya mencantumkan volume air. | `jumlah_kk` dan `jumlah_jiwa` **boleh kosong**; `volume_liter` tetap wajib. Dashboard menampilkan total dari data yang terisi disertai penanda berapa data yang belum lengkap. |
| 6 | **Data susulan untuk tanggal yang sudah lewat** — laporan lapangan kerap baru sampai ke admin beberapa hari kemudian. | Tanggal kejadian disimpan pada `tanggal_penyaluran`, terpisah dari `created_at`. Admin bebas menginput dan mengoreksi data historis; seluruh rekap dan laporan mengelompokkan berdasarkan `tanggal_penyaluran`. Setiap perubahan dicatat pada riwayat perubahan (dibangun di Fase 3). Rincian di §9.3. |
| 7 | **Sebesar apa master data wilayah dapat diubah** | Kabupaten dan kecamatan **hanya dapat dilihat** — datanya berasal dari sumber resmi dan praktis tidak berubah; perubahan dilakukan lewat seeder. Desa/kelurahan **dapat ditambah dan diubah** admin, karena pemekaran wilayah dan perbaikan ejaan sesekali terjadi. |
| 8 | **Penghapusan master data** | Tidak ada penghapusan untuk desa maupun instansi. Keduanya **dinonaktifkan**, sama seperti pola akun pengguna: hilang dari pilihan form penyaluran, tetapi riwayat penyaluran yang menyebutnya tetap utuh. Route `destroy` sengaja tidak didaftarkan. |
| 9 | **Penambahan instansi pelaksana** | Admin **bebas menambah** instansi baru. Di lapangan pelaksananya memang bertambah sewaktu-waktu (Polsek, PDAM, relawan), dan menunggu pengembang akan menghambat pencatatan. |
| 10 | **Bentuk laporan dan export** | **PDF** meniru dokumen infografis kantor (per tanggal → kabupaten → kecamatan → desa, dengan total harian dan Ringkasan Keseluruhan). **Excel** berbentuk tabel datar siap *pivot*. Tanpa kop surat resmi dan tanpa blok tanda tangan. |

### 12.2 Masih Terbuka

| # | Isu | Asumsi sementara yang dipakai | Dampak bila berubah |
|---|---|---|---|
| 1 | **Kompatibilitas `maatwebsite/excel` dengan Laravel 13** — perlu diverifikasi saat instalasi. | Bila belum kompatibel, export memakai `openspout/openspout` langsung (menghasilkan `.xlsx` yang sama) atau CSV sebagai cadangan terakhir. | Kecil — hanya menyentuh kelas `PenyaluranExport`. |
| 2 | **Duplikat penyaluran** — bolehkah satu desa menerima dua kegiatan pada tanggal yang sama. | Diperbolehkan; data nyata memang menunjukkannya. Sistem hanya menampilkan peringatan, tanpa batasan `UNIQUE` di database. | Sedang — bila harus dilarang, perlu menambah *unique constraint*. |
| 3 | **Penerima yang bukan desa** — laporan 25 Agustus 2026 mencatat "SMAN 1 Suwawa Timur" sebagai penerima. | Dicatat pada kolom Keterangan, dengan desa tetap dipilih sebagai lokasi wilayahnya. | Kecil — bila perlu difilter, tambahkan kolom "titik penyaluran". |
| 4 | **Data armada/kendaraan** — laporan asli memuat bagian "Armada tersedia" dan "Kendala yang dihadapi". | Di luar cakupan MVP sesuai PRD bagian 5. Tidak dicatat sistem. | Besar — perlu tabel dan menu baru. |

---

## 13. Setup Pengembangan

```bash
# 1. Dependensi PHP
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (MySQL sudah berjalan lewat Laragon)
#    .env → DB_CONNECTION=mysql, DB_DATABASE=sicatat, DB_USERNAME=root, DB_PASSWORD=
php artisan migrate --seed

# 4. Aset frontend
npm install
npm run dev        # mode pengembangan
npm run build      # untuk produksi

# 5. Jalankan
php artisan serve  # http://localhost:8000
```

Akun awal dari `UserSeeder` (wajib diganti sebelum dipakai sungguhan):

| Role | Username | Password sementara |
|---|---|---|
| Admin | `admin` | `password` |
| Pimpinan | `pimpinan` | `password` |
| Petugas | `petugas` | `password` |

Ketiganya dibuat sebagai password sementara: pada login pertama sistem langsung meminta password baru.

---

## 14. Deployment

Target: server internal instansi atau VPS, dengan Apache/Nginx + PHP-FPM 8.3 + MySQL 8.

Langkah pokok:

1. Salin kode ke server, arahkan *document root* ke folder `public/`.
2. `composer install --no-dev --optimize-autoloader`
3. Isi `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` sesuai domain, kredensial database server.
4. `php artisan key:generate` (sekali saja), lalu `php artisan migrate --force`.
5. `npm ci && npm run build`, lalu unggah folder `public/build`.
6. `php artisan config:cache route:cache view:cache`.
7. Pastikan folder `storage/` dan `bootstrap/cache/` dapat ditulis oleh proses web server.
8. Jadwalkan backup database harian (`mysqldump`) — ini satu-satunya salinan data penyaluran.

---

## 15. Rencana Implementasi Bertahap

| Fase | Lingkup | FR yang dipenuhi |
|---|---|---|
| **1** | Setup project, migrasi, model, autentikasi, role, manajemen pengguna, layout dasar | FR-01, FR-02, FR-03 |
| **2** | Master data: wilayah (kab/kec/desa), instansi | FR-04 – FR-07 |
| **3** | Modul penyaluran: CRUD, riwayat, pencarian, filter, pencatatan riwayat perubahan data (§9.3) | FR-08 – FR-18 |
| **4** | Dashboard: kartu statistik dan grafik bulanan | FR-19 – FR-21 |
| **5** | Laporan, export PDF, export Excel | FR-22 – FR-24 |

Setiap fase diperiksa dan disetujui sebelum lanjut ke fase berikutnya.
