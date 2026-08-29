<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Pengguna mengganti passwordnya sendiri (bagian 7).
 *
 * Password lama diminta sebagai bukti kepemilikan sesi, dan tidak pernah
 * ditampilkan kembali ke layar.
 */
class UbahPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password_sekarang' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:password_sekarang', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password_sekarang' => 'password saat ini',
            'password' => 'password baru',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password_sekarang.current_password' => 'Password saat ini tidak sesuai.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ];
    }
}
