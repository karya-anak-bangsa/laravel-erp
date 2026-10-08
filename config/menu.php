<?php

/*
|--------------------------------------------------------------------------
| Menu Sidebar Admin
|--------------------------------------------------------------------------
|
| Dirender oleh layouts/partials/sidebar. Setiap modul menambah satu grup
| di sini, tanpa mengubah grup modul lain.
|
| - label : teks menu
| - ikon  : nama ikon Font Awesome solid tanpa awalan "fa-". Dashboard memakai
|           landmark; semua menu modul memakai book (pilihan pemilik)
| - rute  : nama rute tujuan
| - aktif : pola nama rute yang membuat menu tampil aktif (default = rute)
|
*/

return [
    [
        'judul' => 'Umum',
        'item' => [
            [
                'label' => 'Dashboard',
                'ikon' => 'landmark',
                'rute' => 'admin.dashboard',
            ],
        ],
    ],
    [
        'judul' => 'Company Profile',
        'item' => [
            [
                'label' => 'Identitas',
                'ikon' => 'book',
                'rute' => 'admin.identitas.edit',
                'aktif' => 'admin.identitas.*',
            ],
            [
                'label' => 'Hero',
                'ikon' => 'book',
                'rute' => 'admin.hero.index',
                'aktif' => 'admin.hero.*',
            ],
            [
                'label' => 'Layanan',
                'ikon' => 'book',
                'rute' => 'admin.layanan.index',
                'aktif' => 'admin.layanan.*',
            ],
        ],
    ],
];
