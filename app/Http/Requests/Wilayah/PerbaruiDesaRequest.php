<?php

namespace App\Http\Requests\Wilayah;

use App\Models\Desa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form Ubah Desa/Kelurahan (FR-06).
 */
class PerbaruiDesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $desa = $this->route('desa');

        return [
            'kecamatan_id' => ['required', 'integer', Rule::exists('kecamatans', 'id')],
            'kode' => ['nullable', 'string', 'max:15', Rule::unique('desas', 'kode')->ignore($desa)],
            // Sama seperti saat menambah: nama hanya perlu unik dalam satu kecamatan.
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('desas', 'nama')
                    ->where(fn ($query) => $query->where('kecamatan_id', $this->input('kecamatan_id')))
                    ->ignore($desa),
            ],
            'jenis' => ['required', Rule::in(array_keys(Desa::daftarJenis()))],
            'aktif' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.unique' => 'Kecamatan ini sudah memiliki desa/kelurahan dengan nama tersebut.',
            'kode.unique' => 'Kode wilayah ini sudah dipakai desa/kelurahan lain.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kecamatan_id' => 'kecamatan',
            'kode' => 'kode wilayah',
            'nama' => 'nama desa/kelurahan',
            'jenis' => 'jenis wilayah',
            'aktif' => 'status',
        ];
    }
}
