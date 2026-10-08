<?php

namespace App\Enums\CompanyProfile;

/**
 * Status artikel: hanya artikel terbit yang tampil di frontend.
 */
enum StatusPublikasi: string
{
    case Draf = 'draf';
    case Terbit = 'terbit';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Terbit => 'Terbit',
        };
    }

    /**
     * Kelas warna komponen .status Gentelella.
     */
    public function kelasStatus(): string
    {
        return match ($this) {
            self::Draf => 'status-yellow',
            self::Terbit => 'status-green',
        };
    }

    /**
     * @return array<string, string> [nilai => label] untuk <x-admin.form-select> dan filter
     */
    public static function opsi(): array
    {
        $opsi = [];

        foreach (self::cases() as $status) {
            $opsi[$status->value] = $status->label();
        }

        return $opsi;
    }
}
