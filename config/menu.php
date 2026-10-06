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
| - ikon  : nama ikon Font Awesome solid tanpa awalan "fa-" (mis. gauge)
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
                'ikon' => 'gauge',
                'rute' => 'admin.dashboard',
            ],
        ],
    ],
    [
        'judul' => 'Kas Perusahaan',
        'item' => [
            [
                'label' => 'Akun Kas',
                'ikon' => 'wallet',
                'rute' => 'admin.akun-kas.index',
                'aktif' => 'admin.akun-kas.*',
            ],
        ],
    ],
];
