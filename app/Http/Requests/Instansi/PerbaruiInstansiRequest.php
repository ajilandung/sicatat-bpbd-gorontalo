<?php

namespace App\Http\Requests\Instansi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi form Ubah Instansi Pelaksana (FR-07).
 */
class PerbaruiInstansiRequest extends FormRequest
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
        return [
            'nama' => ['required', 'string', 'max:150', Rule::unique('instansis', 'nama')->ignore($this->route('instansi'))],
            'singkatan' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30', 'regex:/^[0-9()+\-. ]+$/'],
            'aktif' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.unique' => 'Instansi dengan nama ini sudah terdaftar.',
            'telepon.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, dan tanda + ( ) - .',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'nama instansi',
            'singkatan' => 'singkatan',
            'alamat' => 'alamat',
            'telepon' => 'nomor telepon',
            'aktif' => 'status',
        ];
    }
}
