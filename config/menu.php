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
    [
        'judul' => 'Kas Perusahaan',
        'item' => [
            [
                'label' => 'Transaksi Kas',
                'ikon' => 'money-bill-transfer',
                'rute' => 'admin.transaksi-kas.index',
                'aktif' => 'admin.transaksi-kas.*',
            ],
            [
                'label' => 'Laporan Arus Kas',
                'ikon' => 'chart-column',
                'rute' => 'admin.laporan-arus-kas.index',
            ],
            [
                'label' => 'Akun Kas',
                'ikon' => 'wallet',
                'rute' => 'admin.akun-kas.index',
                'aktif' => 'admin.akun-kas.*',
            ],
            [
                'label' => 'Kategori Transaksi',
                'ikon' => 'tags',
                'rute' => 'admin.kategori-transaksi.index',
                'aktif' => 'admin.kategori-transaksi.*',
            ],
        ],
    ],
];
