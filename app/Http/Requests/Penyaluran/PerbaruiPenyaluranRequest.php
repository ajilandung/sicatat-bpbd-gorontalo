<?php

namespace App\Http\Requests\Penyaluran;

/**
 * Validasi form ubah kegiatan penyaluran (FR-09).
 *
 * Aturannya sama persis dengan form tambah: tidak ada kolom unik yang perlu
 * dikecualikan, dan koreksi data historis memang harus tunduk pada batas yang
 * sama. Kelas terpisah dipakai agar penamaannya seragam dengan modul lain dan
 * agar aturan khusus pengubahan punya tempat bila suatu saat diperlukan.
 */
class PerbaruiPenyaluranRequest extends SimpanPenyaluranRequest {}
