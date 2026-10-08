<?php

namespace App\Enums\CompanyProfile;

/**
 * Gaya tombol CTA pada hero: menentukan tampilan tombol di frontend.
 */
enum GayaCta: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Utama',
            self::Secondary => 'Sekunder',
        };
    }

    /**
     * @return array<string, string> [nilai => label] untuk <x-admin.form-select>
     */
    public static function opsi(): array
    {
        $opsi = [];

        foreach (self::cases() as $gaya) {
            $opsi[$gaya->value] = $gaya->label();
        }

        return $opsi;
    }
}
