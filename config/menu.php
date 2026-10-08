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
| - badge : (opsional) kunci angka badge yang dihitung App\View\Composers\MenuComposer.
|           Bukan closure, karena config harus bisa di-cache (php artisan optimize)
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
            [
                'label' => 'Portofolio',
                'ikon' => 'book',
                'rute' => 'admin.portofolio.index',
                'aktif' => 'admin.portofolio.*',
            ],
            [
                'label' => 'FAQ',
                'ikon' => 'book',
                'rute' => 'admin.faq.index',
                'aktif' => 'admin.faq.*',
            ],
            [
                'label' => 'Kontak Kami',
                'ikon' => 'book',
                'rute' => 'admin.kontak-kami.index',
                'aktif' => 'admin.kontak-kami.*',
                'badge' => 'kontak-kami-belum-dibaca',
            ],
        ],
    ],
];
