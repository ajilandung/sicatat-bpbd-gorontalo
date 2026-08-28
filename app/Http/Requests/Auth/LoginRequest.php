<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'login' => 'username atau email',
            'password' => 'password',
        ];
    }

    /**
     * Memeriksa kredensial dan memulai sesi (FR-01).
     *
     * Sesuai PRD 8.1, pengguna boleh masuk memakai username atau email.
     * Akun yang sudah dinonaktifkan admin tidak dapat masuk.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->pastikanTidakDibatasi();

        $isian = (string) $this->input('login');
        $kolom = filter_var($isian, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $kredensial = [
            $kolom => $isian,
            'password' => (string) $this->input('password'),
            'aktif' => true,
        ];

        if (! Auth::attempt($kredensial, $this->boolean('remember'))) {
            RateLimiter::hit($this->kunciPembatas());

            throw ValidationException::withMessages([
                'login' => 'Username/email atau password tidak sesuai.',
            ]);
        }

        RateLimiter::clear($this->kunciPembatas());
    }

    /**
     * Membatasi percobaan login menjadi 5 kali gagal per menit.
     *
     * @throws ValidationException
     */
    protected function pastikanTidakDibatasi(): void
    {
        if (! RateLimiter::tooManyAttempts($this->kunciPembatas(), 5)) {
            return;
        }

        $detik = RateLimiter::availableIn($this->kunciPembatas());

        throw ValidationException::withMessages([
            'login' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$detik} detik.",
        ]);
    }

    protected function kunciPembatas(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('login')).'|'.$this->ip());
    }
}
