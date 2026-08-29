<?php

namespace App\Policies;

use App\Models\User;

/**
 * Aturan otorisasi Manajemen Pengguna (FR-02).
 *
 * Route sudah dijaga middleware `role:admin`; policy ini menangani aturan
 * yang bergantung pada objek, yaitu larangan admin mengunci dirinya sendiri
 * di luar sistem.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin tidak boleh menonaktifkan akunnya sendiri.
     */
    public function ubahStatus(User $user, User $target): bool
    {
        return $user->isAdmin() && ! $user->is($target);
    }

    /**
     * Reset password milik orang lain. Password sendiri diganti lewat menu Ubah Password.
     */
    public function resetPassword(User $user, User $target): bool
    {
        return $user->isAdmin() && ! $user->is($target);
    }
}
