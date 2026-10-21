<?php

namespace App\Enums\Sistem;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Peran akun panel admin: Super Administrator membuka semua menu, Administrator hanya Dashboard,
 * Company Profile, dan Profil Saya (tanpa Pengaturan Sistem).
 */
enum PeranPengguna: string
{
    use MenyediakanOpsi;

    case SuperAdministrator = 'super_administrator';
    case Administrator = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdministrator => 'Super Administrator',
            self::Administrator => 'Administrator',
        };
    }
}
