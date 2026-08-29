<?php

namespace App\Http\Requests\Pengguna;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Admin menetapkan password sementara baru untuk pengguna lain.
 */
class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resetPassword', $this->route('pengguna')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password' => 'password sementara',
        ];
    }
}
