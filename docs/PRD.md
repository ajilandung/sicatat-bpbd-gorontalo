# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Sistem Informasi Pencatatan Penyaluran Bantuan Air Bersih

| | |
|---|---|
| **Instansi** | BPBD Provinsi Gorontalo |
| **Versi** | MVP 1.0 |
| **Tujuan** | Proyek Magang / Pengembangan Sistem Internal |
| **Dokumen sumber** | [`sumber/PRD_Sistem_Informasi_Pencatatan_Penyaluran_Air_Bersih_BPBD_Provinsi_Gorontalo.docx`](sumber/PRD_Sistem_Informasi_Pencatatan_Penyaluran_Air_Bersih_BPBD_Provinsi_Gorontalo.docx) |

---

## 1. Latar Belakang

BPBD Provinsi Gorontalo melakukan kegiatan penyaluran bantuan air bersih kepada masyarakat di wilayah yang mengalami kekurangan air atau terdampak kondisi tertentu.

Saat ini, data penyaluran dikumpulkan dari petugas lapangan dan kemudian direkap oleh admin menggunakan Microsoft Excel. Data yang dicatat meliputi lokasi penyaluran, jumlah masyarakat terdampak, jumlah air yang disalurkan, serta instansi yang terlibat dalam kegiatan penyaluran.

Proses pencatatan menggunakan Excel memiliki beberapa kendala, seperti data berpotensi tercecer atau hilang, sulit mencari riwayat penyaluran, proses pembuatan laporan masih manual, data dari lapangan lambat masuk, serta sulit mengetahui wilayah yang telah menerima bantuan.

Oleh karena itu, diperlukan Sistem Informasi Pencatatan Penyaluran Bantuan Air Bersih berbasis web yang digunakan secara internal oleh BPBD Provinsi Gorontalo untuk menyimpan, mengelola, mencari, memantau, dan menghasilkan laporan data penyaluran secara lebih terstruktur.

---

## 2. Permasalahan

- Data penyaluran masih direkap menggunakan Excel.
- Data berpotensi tercecer atau hilang.
- Riwayat penyaluran sulit dicari.
- Pembuatan laporan membutuhkan proses manual.
- Data dari petugas lapangan tidak langsung tersimpan dalam sistem terpusat.
- Sulit mengetahui wilayah yang telah menerima bantuan.
- Sulit melihat total volume air yang telah disalurkan dalam periode tertentu.
- Belum terdapat dashboard untuk memantau aktivitas penyaluran secara ringkas.

---

## 3. Tujuan Sistem

- Memusatkan data penyaluran bantuan air bersih dalam satu sistem.
- Mempermudah admin dalam mencatat data penyaluran.
- Mempermudah pencarian riwayat penyaluran.
- Mengetahui wilayah yang telah menerima bantuan.
- Menampilkan statistik penyaluran melalui dashboard.
- Mempermudah pembuatan laporan penyaluran.
- Memungkinkan laporan diekspor ke PDF dan Excel.
- Membantu pimpinan memantau kegiatan penyaluran melalui dashboard dan laporan.

---

## 4. Target Pengguna

Sistem digunakan secara internal oleh BPBD Provinsi Gorontalo.

| Pengguna | Hak Akses / Tugas |
|---|---|
| **Admin** | Mengelola seluruh data sistem dan data penyaluran. |
| **Petugas** | Menginput data hasil penyaluran dari lapangan, dan mengoreksi data yang ia input sendiri. |
| **Pimpinan** | Melihat dashboard, statistik, riwayat penyaluran, dan laporan. |

**Alur pencatatan:**

> Petugas lapangan menginput data penyalurannya sendiri ke sistem — atau menyerahkannya kepada admin untuk diinput → data tersimpan secara terpusat → admin memeriksa dan melengkapi seluruh data → pimpinan melihat dashboard dan laporan.

---

## 5. Ruang Lingkup MVP

Versi pertama sistem berfokus pada kebutuhan utama pencatatan dan pelaporan.

**Fitur yang termasuk dalam MVP:**

- Login
- Dashboard
- Manajemen pengguna
- Data wilayah
- Data instansi pelaksana
- Pencatatan penyaluran air bersih
- Riwayat penyaluran
- Pencarian dan filter data
- Laporan
- Export PDF
- Export Excel

**Fitur yang belum termasuk dalam MVP:**

- GIS / Peta
- Tracking kendaraan
- Manajemen kendaraan
- Monitoring GPS
- Notifikasi otomatis
- Aplikasi mobile
- Input data langsung secara real-time dari lapangan

---

## 6. Alur Bisnis Sistem

1. Wilayah mengalami kekurangan air
2. BPBD menerima informasi
3. BPBD menentukan lokasi penerima bantuan
4. Petugas melakukan penyaluran air bersih
5. Petugas mencatat hasil penyaluran
6. Data diberikan kepada Admin
7. Admin menginput data ke sistem
8. Data tersimpan dalam database
9. Dashboard diperbarui
10. Pimpinan melihat dashboard atau laporan

---

## 7. Struktur Data Wilayah

Data wilayah menggunakan struktur administratif sebagai berikut:

```
Kabupaten/Kota → Kecamatan → Desa/Kelurahan
```

**Contoh:** Kabupaten Bone Bolango → Kecamatan Bone Pantai → Desa Tongo

---

## 8. Fitur Utama Sistem

### 8.1 Autentikasi / Login

Pengguna harus login sebelum mengakses sistem menggunakan email atau username dan password. Setelah login, pengguna diarahkan sesuai dengan hak aksesnya.

### 8.2 Dashboard

Dashboard menampilkan ringkasan data penyaluran, meliputi:

- Total penyaluran air
- Total volume air yang telah disalurkan
- Jumlah wilayah penerima
- Jumlah kegiatan penyaluran bulan ini
- Grafik penyaluran air per bulan
- Wilayah yang paling sering menerima bantuan
- Data penyaluran terbaru

### 8.3 Manajemen Data Wilayah

Admin dapat mengelola data Kabupaten/Kota, Kecamatan, dan Desa/Kelurahan.

Alur pemilihan lokasi pada form:

1. Pilih Kabupaten/Kota
2. Sistem menampilkan Kecamatan sesuai Kabupaten/Kota
3. Pilih Kecamatan
4. Sistem menampilkan Desa/Kelurahan sesuai Kecamatan
5. Pilih Desa/Kelurahan

### 8.4 Data Instansi Pelaksana

Admin dapat menambah, mengubah, menghapus, dan melihat daftar instansi pelaksana yang terlibat dalam kegiatan penyaluran.

### 8.5 Pencatatan Penyaluran Air Bersih

Setiap satu desa memiliki satu data penyaluran. Model input per desa dipilih agar data lebih mudah dicari, difilter, dan direkap.

| Kelompok Data | Field |
|---|---|
| Informasi Penyaluran | Tanggal penyaluran |
| Lokasi | Kabupaten/Kota |
| Lokasi | Kecamatan |
| Lokasi | Desa/Kelurahan |
| Data Penerima | Jumlah KK terdampak |
| Data Penerima | Jumlah jiwa terdampak |
| Data Air | Jumlah air tersalur |
| Data Air | Satuan: Liter |
| Pelaksana | Instansi pelaksana |
| Informasi Sistem | Petugas yang menginput |
| Informasi Tambahan | Keterangan |

### 8.6 Riwayat Penyaluran

Sistem menampilkan seluruh data penyaluran dalam bentuk tabel. Admin dapat menambah, melihat detail, mengubah, dan menghapus data penyaluran. Petugas dapat menambah dan melihat detail seluruh data, tetapi hanya dapat mengubah data yang ia input sendiri dan tidak dapat menghapus.

| Tanggal | Kabupaten | Kecamatan | Desa | KK | Jiwa | Air | Pelaksana |
|---|---|---|---|---|---|---|---|
| 01/08/2026 | Gorontalo | Pulubala | Molyonegoro | 220 | 459 | 4.000 L | BPBD Kab. Gorontalo |
| 01/08/2026 | Gorontalo | Bongomeme | Liyoto | 187 | 561 | 4.000 L | BPBD Kab. Gorontalo |

### 8.7 Pencarian dan Filter

- Periode tanggal
- Kabupaten/Kota
- Kecamatan
- Desa/Kelurahan
- Instansi pelaksana
- Petugas
- Jumlah penyaluran

### 8.8 Laporan

Sistem dapat menghasilkan laporan berdasarkan filter yang dipilih. Informasi laporan meliputi:

- Periode laporan
- Total air tersalur
- Jumlah wilayah penerima
- Jumlah KK terdampak
- Jumlah jiwa terdampak
- Rincian data penyaluran

### 8.9 Export Laporan

Laporan dapat diekspor ke format:

- **PDF** untuk dokumentasi dan pencetakan
- **Excel** untuk pengolahan dan rekapitulasi data

---

## 9. Hak Akses Pengguna

| Role | Akses |
|---|---|
| **Admin** | Dashboard, data pengguna, data wilayah, data instansi, data penyaluran, tambah/edit/hapus seluruh data, riwayat, laporan, export PDF dan Excel. |
| **Petugas** | Dashboard, melihat seluruh data penyaluran, menambah data penyaluran baru, serta mengubah data dan foto dokumentasi **yang ia input sendiri**. Tidak dapat mengubah data milik pengguna lain, menghapus data, membuka laporan, maupun mengelola pengguna dan master data. |
| **Pimpinan** | Melihat dashboard, statistik, riwayat penyaluran, dan laporan tanpa mengubah atau menghapus data. |

Batas kepemilikan pada baris Petugas ditegakkan di sisi server lewat `PenyaluranPolicy`, bukan dengan menyembunyikan tombol: membuka atau mengirim perubahan atas data milik pengguna lain — termasuk dengan mengetik URL secara langsung — ditolak dengan galat 403.

---

## 10. Kebutuhan Fungsional

| ID | Kebutuhan |
|---|---|
| FR-01 | Sistem harus menyediakan fitur login. |
| FR-02 | Sistem harus membedakan hak akses pengguna. |
| FR-03 | Admin dapat mengelola data pengguna. |
| FR-04 | Admin dapat mengelola data Kabupaten/Kota. |
| FR-05 | Admin dapat mengelola data Kecamatan. |
| FR-06 | Admin dapat mengelola data Desa/Kelurahan. |
| FR-07 | Admin dapat mengelola data instansi pelaksana. |
| FR-08 | Admin dapat menambahkan data penyaluran. |
| FR-09 | Admin dapat mengubah data penyaluran. |
| FR-10 | Admin dapat menghapus data penyaluran. |
| FR-11 | Sistem menyimpan jumlah KK terdampak. |
| FR-12 | Sistem menyimpan jumlah jiwa terdampak. |
| FR-13 | Sistem menyimpan jumlah air tersalur dalam liter. |
| FR-14 | Sistem menyimpan instansi pelaksana. |
| FR-15 | Sistem menampilkan riwayat penyaluran. |
| FR-16 | Sistem dapat melakukan pencarian data. |
| FR-17 | Sistem dapat memfilter data berdasarkan wilayah. |
| FR-18 | Sistem dapat memfilter data berdasarkan periode. |
| FR-19 | Sistem menghitung total air tersalur. |
| FR-20 | Sistem menghitung jumlah wilayah penerima. |
| FR-21 | Sistem menampilkan grafik penyaluran bulanan. |
| FR-22 | Sistem menghasilkan laporan. |
| FR-23 | Sistem dapat export laporan ke PDF. |
| FR-24 | Sistem dapat export laporan ke Excel. |

---

## 11. Kebutuhan Non-Fungsional

- **Berbasis Web** — Sistem dapat diakses melalui browser.
- **Responsif** — Tampilan dapat digunakan pada laptop, desktop, dan tablet.
- **Keamanan** — Pengguna harus login, password disimpan secara aman, dan hak akses dibatasi berdasarkan role.
- **Kemudahan Penggunaan** — Antarmuka harus sederhana, mudah dipahami, tidak memiliki proses input yang rumit, dan membantu meminimalkan kesalahan input.

---

## 12. Scope MVP

| Termasuk | Belum Termasuk |
|---|---|
| Login | GIS / Peta |
| Role pengguna | GPS Tracking |
| Dashboard | Data kendaraan |
| Data wilayah | Tracking mobil tangki |
| Data instansi | Aplikasi mobile |
| Input penyaluran | Notifikasi otomatis |
| Riwayat penyaluran | Integrasi API eksternal |
| Pencarian | Input real-time langsung dari lapangan |
| Filter | — |
| Laporan | — |
| Export PDF | — |
| Export Excel | — |

---

## 13. Kesimpulan

Sistem yang akan dikembangkan adalah Sistem Informasi Pencatatan Penyaluran Bantuan Air Bersih Berbasis Web pada BPBD Provinsi Gorontalo. Sistem berfungsi untuk membantu proses pencatatan, penyimpanan, pencarian, monitoring, dan pembuatan laporan kegiatan penyaluran bantuan air bersih secara terpusat.

---

## 14. Tahapan Pengembangan Selanjutnya

1. PRD
2. Technical Architecture
3. Database Schema / ERD
4. Laravel Project Setup
5. Migration & Database
6. Authentication & Role
7. Implementasi Fitur
8. Testing
9. Deployment

---

## Referensi Data Awal

PRD ini disusun berdasarkan kebutuhan yang telah disampaikan dan contoh dokumen *"Update Kegiatan Penyaluran Bantuan Air Bersih 1–24 Agustus 2026"*, yang memuat data tanggal, wilayah, KK dan jiwa terdampak, volume air tersalur, pelaksana, ringkasan keseluruhan, serta informasi operasional.
