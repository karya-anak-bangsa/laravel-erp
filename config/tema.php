<?php

/*
|--------------------------------------------------------------------------
| Template frontend publik
|--------------------------------------------------------------------------
|
| Nilai dua template bawaan (satu per jenis), dengan kunci sama seperti kolom tb_template
| (docs/DATABASE.md). Sejak Fase 4 langkah 3 template aktif dikelola admin di Pengaturan
| Sistem › Manajemen Template; nilai di sini menjadi data awal TemplateSeeder, nilai pilihan
| "Default" di form template, sekaligus cadangan bila database tidak terbaca atau belum ada
| template aktif. View tidak membaca config ini langsung — selalu lewat
| App\Services\CompanyProfile\TemaService. Nilai sekarang = data template lokal yang
| ditetapkan pemilik sebagai bawaan (2026-10-14).
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
            'font' => 'geist',
            'sudut' => 'sedang',
            // Ukuran desktop navbar, tombol, dan judul seksi; naik satu tingkat otomatis di layar ≥1920px.
            'skala' => 'standar',
            // Lebar isi halaman di monitor ≥1920px: standar (1280px) atau lebar (1440px).
            'lebar_konten' => 'standar',
            'ketebalan_judul' => 'semibold',
            // Bayangan tombol, kartu, isian, dan bingkai gambar hero: tanpa, halus, atau tegas.
            'bayangan' => 'halus',
        ],
        'monochrome' => [
            'nama' => 'TKAB Monochrome',
            'warna_utama' => null,
            'warna_aksen' => null,
            'nada_dasar' => 'netral',
            'font' => 'geist',
            'sudut' => 'sedang',
            'skala' => 'standar',
            'lebar_konten' => 'standar',
            'ketebalan_judul' => 'semibold',
            'bayangan' => 'tanpa',
        ],
    ],

];
