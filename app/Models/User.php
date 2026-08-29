<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'aktif', 'harus_ganti_password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PETUGAS = 'petugas';

    public const ROLE_PIMPINAN = 'pimpinan';

    /**
     * Seluruh role beserta labelnya. Dipakai untuk pilihan pada form dan filter.
     *
     * @return array<string, string>
     */
    public static function daftarRole(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_PETUGAS => 'Petugas',
            self::ROLE_PIMPINAN => 'Pimpinan',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
            'harus_ganti_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Data penyaluran yang diinput oleh pengguna ini.
     */
    public function penyalurans(): HasMany
    {
        return $this->hasMany(Penyaluran::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPetugas(): bool
    {
        return $this->role === self::ROLE_PETUGAS;
    }

    public function isPimpinan(): bool
    {
        return $this->role === self::ROLE_PIMPINAN;
    }

    public function labelRole(): string
    {
        return self::daftarRole()[$this->role] ?? 'Petugas';
    }

    /**
     * Dashboard tujuan sesuai role, dipakai setelah login dan oleh menu sidebar.
     */
    public function routeDashboard(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'dashboard.admin',
            self::ROLE_PIMPINAN => 'dashboard.pimpinan',
            default => 'dashboard.petugas',
        };
    }

    /**
     * Pencarian pengguna berdasarkan nama, username, atau email (Manajemen Pengguna).
     *
     * @param  Builder<User>  $query
     */
    public function scopeCari(Builder $query, ?string $kata): void
    {
        $kata = trim((string) $kata);

        $query->when($kata !== '', function (Builder $query) use ($kata) {
            $query->where(function (Builder $query) use ($kata) {
                $query->where('name', 'like', "%{$kata}%")
                    ->orWhere('username', 'like', "%{$kata}%")
                    ->orWhere('email', 'like', "%{$kata}%");
            });
        });
    }

    /**
     * @param  Builder<User>  $query
     */
    public function scopeRole(Builder $query, ?string $role): void
    {
        $query->when(
            $role !== null && array_key_exists($role, self::daftarRole()),
            fn (Builder $query) => $query->where('role', $role),
        );
    }

    /**
     * Filter status akun: 'aktif' atau 'nonaktif'.
     *
     * @param  Builder<User>  $query
     */
    public function scopeStatus(Builder $query, ?string $status): void
    {
        $query->when(
            in_array($status, ['aktif', 'nonaktif'], true),
            fn (Builder $query) => $query->where('aktif', $status === 'aktif'),
        );
    }
}
