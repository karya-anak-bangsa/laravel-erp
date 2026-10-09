<?php

/*
|--------------------------------------------------------------------------
| Template frontend publik
|--------------------------------------------------------------------------
|
| Nilai dua template bawaan (satu per jenis). Kuncinya sama dengan kolom tb_template
| (docs/DATABASE.md) agar Fase 4 langkah 3 cukup memindahkannya ke database; setelah itu
| nilai di sini menjadi data awal sekaligus cadangan. View tidak membaca config ini
| langsung — selalu lewat App\Services\CompanyProfile\TemaService.
|
*/

return [

    // Cookie pilihan pengunjung; ditulis JS, jadi dikecualikan dari enkripsi (bootstrap/app.php).
    'cookie' => 'tema',

    'template' => [
        'full_color' => [
            'nama' => 'TKAB Full Color',
            'warna_utama' => '#15253F',
            'warna_aksen' => '#CB1839',
            'nada_dasar' => null,
            'font' => 'plus_jakarta_sans',
            'sudut' => 'sedang',
        ],
        'monochrome' => [
            'nama' => 'TKAB Monochrome',
            'warna_utama' => null,
            'warna_aksen' => null,
            'nada_dasar' => 'netral',
            'font' => 'geist',
            'sudut' => 'sedang',
        ],
    ],

];
