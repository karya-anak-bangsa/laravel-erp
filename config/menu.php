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
                'ikon' => 'house',
                'rute' => 'admin.dashboard',
            ],
        ],
    ],
];
