<?php

namespace App\Enums\Kas;

enum JenisAkunKas: string
{
    case Tunai = 'tunai';
    case Bank = 'bank';
    case EWallet = 'e_wallet';

    public function label(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai',
            self::Bank => 'Bank',
            self::EWallet => 'E-Wallet',
        };
    }

    // Akhiran kelas .chip-* Gentelella untuk penanda jenis di tabel.
    public function warna(): string
    {
        return match ($this) {
            self::Tunai => 'green',
            self::Bank => 'blue',
            self::EWallet => 'purple',
        };
    }

    /**
     * Pilihan untuk <x-admin.form-select>.
     *
     * @return array<string, string>
     */
    public static function opsi(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $jenis) => [$jenis->value => $jenis->label()])
            ->all();
    }
}
