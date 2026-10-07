<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class FilterTanggal
{
    /**
     * Tanggal Y-m-d dari query string filter. Nilai kosong, format lain, atau tanggal
     * yang tidak ada (2026-02-31) menghasilkan null agar filter diabaikan, bukan error 500.
     */
    public static function parse(?string $nilai): ?CarbonImmutable
    {
        if ($nilai === null || preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai) !== 1) {
            return null;
        }

        $tanggal = CarbonImmutable::createFromFormat('!Y-m-d', $nilai);

        return $tanggal && $tanggal->format('Y-m-d') === $nilai ? $tanggal : null;
    }
}
