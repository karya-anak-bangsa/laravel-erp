<?php

namespace App\Support;

class FormatRupiah
{
    /**
     * Rp 1.250.000 — sen hanya ditampilkan bila tidak bulat (Rp 1.250.000,50).
     */
    public static function format(int|float|string|null $nilai): string
    {
        $angka = round((float) $nilai, 2);
        $desimal = fmod($angka, 1.0) == 0.0 ? 0 : 2;
        $teks = 'Rp '.number_format(abs($angka), $desimal, ',', '.');

        return $angka < 0 ? '-'.$teks : $teks;
    }
}
