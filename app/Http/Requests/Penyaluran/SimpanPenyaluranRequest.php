<?php

namespace App\Http\Requests\Penyaluran;

use App\Models\Penyaluran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form input kegiatan penyaluran (FR-08, FR-11 sampai FR-14).
 *
 * Route sudah dijaga middleware `role:admin,petugas`; pemeriksaan di sini
 * adalah lapis kedua yang menutup jalur POST langsung, sama seperti pola pada
 * Master Data dan Manajemen Pengguna.
 */
class SimpanPenyaluranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Penyaluran::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Tanggal kegiatan, bukan tanggal input. Tanggal yang sudah lewat
            // sengaja dibiarkan terbuka karena laporan lapangan kerap baru
            // sampai ke admin beberapa hari kemudian (§9.3). Satu-satunya
            // batas adalah hari ini, semata untuk menangkap salah ketik tahun.
            'tanggal_penyaluran' => ['required', 'date', 'before_or_equal:today'],

            // Satu kegiatan dapat mencakup beberapa desa dengan satu angka
            // gabungan, sehingga penerima selalu berupa daftar.
            'desa_id' => ['required', 'array', 'min:1'],
            'desa_id.*' => ['integer', 'distinct', Rule::exists('desas', 'id')],

            // Pelaksananya pun kerap lebih dari satu instansi sekaligus.
            'instansi_id' => ['required', 'array', 'min:1'],
            'instansi_id.*' => ['integer', 'distinct', Rule::exists('instansis', 'id')],

            // KK dan jiwa boleh kosong: banyak entri laporan hanya mencantumkan
            // volume air. Batas atas mengikuti kapasitas kolom `int unsigned`.
            'jumlah_kk' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'jumlah_jiwa' => ['nullable', 'integer', 'min:0', 'max:4294967295'],

            'volume_liter' => ['required', 'integer', 'min:1', 'max:4294967295'],

            'keterangan' => ['nullable', 'string', 'max:1000'],

            // Penanda bahwa admin sudah melihat peringatan kegiatan serupa
            // pada tanggal yang sama dan tetap memilih melanjutkan.
            'konfirmasi_duplikat' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal_penyaluran.before_or_equal' => 'Tanggal penyaluran tidak boleh melewati hari ini.',
            'desa_id.required' => 'Pilih minimal satu desa/kelurahan penerima.',
            'desa_id.min' => 'Pilih minimal satu desa/kelurahan penerima.',
            'desa_id.*.exists' => 'Salah satu desa/kelurahan yang dipilih tidak ditemukan.',
            'instansi_id.required' => 'Pilih minimal satu instansi pelaksana.',
            'instansi_id.min' => 'Pilih minimal satu instansi pelaksana.',
            'instansi_id.*.exists' => 'Salah satu instansi yang dipilih tidak ditemukan.',
            'volume_liter.min' => 'Jumlah air tersalur harus lebih dari 0 liter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tanggal_penyaluran' => 'tanggal penyaluran',
            'desa_id' => 'desa/kelurahan penerima',
            'instansi_id' => 'instansi pelaksana',
            'jumlah_kk' => 'jumlah KK terdampak',
            'jumlah_jiwa' => 'jumlah jiwa terdampak',
            'volume_liter' => 'jumlah air tersalur',
            'keterangan' => 'keterangan',
        ];
    }

    /**
     * Isian yang benar-benar disimpan ke tabel `penyalurans`. Desa dan
     * instansi tidak termasuk karena keduanya disimpan lewat tabel penghubung.
     *
     * @return array<string, mixed>
     */
    public function dataPenyaluran(): array
    {
        return $this->safe()->only([
            'tanggal_penyaluran',
            'jumlah_kk',
            'jumlah_jiwa',
            'volume_liter',
            'keterangan',
        ]);
    }

    /**
     * @return array<int, int>
     */
    public function desaIds(): array
    {
        return array_map('intval', $this->validated('desa_id', []));
    }

    /**
     * @return array<int, int>
     */
    public function instansiIds(): array
    {
        return array_map('intval', $this->validated('instansi_id', []));
    }

    /**
     * Apakah admin sudah menyetujui peringatan kegiatan serupa.
     */
    public function duplikatDikonfirmasi(): bool
    {
        return $this->boolean('konfirmasi_duplikat');
    }
}
