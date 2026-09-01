<?php

namespace App\Http\Requests\Penyaluran;

/**
 * Validasi form ubah kegiatan penyaluran (FR-09).
 *
 * Aturan validasinya sama persis dengan form tambah: tidak ada kolom unik yang
 * perlu dikecualikan, dan koreksi data historis memang harus tunduk pada batas
 * yang sama. Yang berbeda hanya otorisasinya — mengubah bergantung pada siapa
 * pemilik barisnya, sedangkan menambah tidak.
 */
class PerbaruiPenyaluranRequest extends SimpanPenyaluranRequest
{
    /**
     * Petugas hanya boleh mengoreksi kegiatan yang ia input sendiri.
     * Diperiksa di sini agar permintaan PUT langsung — tanpa pernah membuka
     * form — tetap ditolak.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('penyaluran')) ?? false;
    }
}
