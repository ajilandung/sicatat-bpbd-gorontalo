<?php

namespace App\Http\Requests\Pengguna;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi form Edit Pengguna. Password tidak ikut diubah di sini —
 * gunakan aksi Reset Password.
 */
class PerbaruiPenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('pengguna')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pengguna = $this->route('pengguna');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($pengguna)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna)],
            'role' => ['required', Rule::in(array_keys(User::daftarRole()))],
            'aktif' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $pengguna = $this->route('pengguna');

            if (! $this->user()->is($pengguna)) {
                return;
            }

            // Tanpa penjagaan ini, admin terakhir bisa mengunci dirinya sendiri
            // di luar sistem dan tidak ada lagi yang bisa mengelola pengguna.
            if ($this->input('role') !== $pengguna->role) {
                $validator->errors()->add('role', 'Anda tidak dapat mengubah role akun Anda sendiri.');
            }

            if (! $this->boolean('aktif')) {
                $validator->errors()->add('aktif', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'username' => 'username',
            'email' => 'email',
            'role' => 'role',
            'aktif' => 'status akun',
        ];
    }
}
