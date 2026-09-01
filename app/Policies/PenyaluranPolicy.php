<?php

namespace App\Policies;

use App\Models\Penyaluran;
use App\Models\User;

/**
 * Aturan otorisasi data penyaluran (FR-02, FR-08 sampai FR-10).
 *
 * Sejak petugas diberi kemampuan menginput, hak akses data penyaluran tidak
 * lagi cukup ditentukan oleh role saja — ia bergantung pada **siapa pemilik
 * barisnya**. Petugas hanya boleh mengoreksi kegiatan yang ia input sendiri,
 * sedangkan admin boleh atas seluruh kegiatan.
 *
 * Aturan kepemilikan itu sengaja hanya ditulis di kelas ini, lalu dipakai
 * ulang oleh route, controller, FormRequest, dan tampilan. Dengan begitu
 * menyembunyikan tombol dan menjaga jalur POST/PUT memakai satu sumber aturan
 * yang sama — tidak mungkin keduanya berbeda pendapat.
 */
class PenyaluranPolicy
{
    /**
     * Menambah kegiatan baru. Pimpinan hanya membaca.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPetugas();
    }

    /**
     * Mengoreksi isi kegiatan (FR-09).
     */
    public function update(User $user, Penyaluran $penyaluran): bool
    {
        return ! $penyaluran->trashed() && $this->adminAtauPemilik($user, $penyaluran);
    }

    /**
     * Menghapus kegiatan (FR-10) — khusus admin, termasuk untuk data yang
     * diinput petugas. Penghapusan menyembunyikan kegiatan dari seluruh rekap
     * dan laporan, jadi keputusannya ada pada pemegang tanggung jawab data.
     */
    public function delete(User $user, Penyaluran $penyaluran): bool
    {
        return $user->isAdmin();
    }

    /**
     * Membuka Data Terhapus dan memulihkan isinya.
     */
    public function pulihkan(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menambah dan menghapus foto dokumentasi (§9.4). Mengikuti aturan yang
     * sama dengan mengoreksi datanya: petugas yang mengerjakan kegiatan di
     * lapangan adalah pemegang fotonya.
     */
    public function kelolaFoto(User $user, Penyaluran $penyaluran): bool
    {
        return ! $penyaluran->trashed() && $this->adminAtauPemilik($user, $penyaluran);
    }

    /**
     * Melihat panel Riwayat Perubahan pada halaman detail. Yang membacanya
     * adalah pihak yang juga berwenang mengoreksi datanya, sehingga petugas
     * dapat mengetahui bila kegiatannya sendiri diperbaiki orang lain.
     */
    public function lihatRiwayat(User $user, Penyaluran $penyaluran): bool
    {
        return $this->adminAtauPemilik($user, $penyaluran);
    }

    /**
     * Pemilik baris adalah pengguna yang menginputnya (`penyalurans.user_id`).
     *
     * Perhatikan bahwa data terhapus diperiksa terpisah oleh `update` dan
     * `kelolaFoto`, bukan di sini: kegiatan yang sudah dihapus tidak boleh
     * diubah siapa pun sebelum dipulihkan, tetapi riwayat perubahannya justru
     * paling perlu terbaca pada keadaan itu — di sanalah tercatat siapa yang
     * menghapusnya.
     */
    private function adminAtauPemilik(User $user, Penyaluran $penyaluran): bool
    {
        return $user->isAdmin()
            || ($user->isPetugas() && $penyaluran->user_id === $user->getKey());
    }
}
