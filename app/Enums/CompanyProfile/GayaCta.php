<?php

namespace App\Enums\CompanyProfile;

use App\Enums\Concerns\MenyediakanOpsi;

/**
 * Gaya tombol CTA pada hero: menentukan tampilan tombol di frontend.
 */
enum GayaCta: string
{
    use MenyediakanOpsi;

    case Primary = 'primary';
    case Secondary = 'secondary';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Utama',
            self::Secondary => 'Sekunder',
        };
    }
}
