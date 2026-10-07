<?php

namespace App\Enums\Kas;

enum JenisTransaksi: string
{
    case Pemasukan = 'pemasukan';
    case Pengeluaran = 'pengeluaran';

    public function label(): string
    {
        return match ($this) {
            self::Pemasukan => 'Pemasukan',
            self::Pengeluaran => 'Pengeluaran',
        };
    }

    // Akhiran kelas .chip-* Gentelella untuk penanda jenis di tabel.
    public function warna(): string
    {
        return match ($this) {
            self::Pemasukan => 'green',
            self::Pengeluaran => 'red',
        };
    }

    // Awalan nomor transaksi: Kas Masuk / Kas Keluar.
    public function awalanNomor(): string
    {
        return match ($this) {
            self::Pemasukan => 'KM',
            self::Pengeluaran => 'KK',
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
