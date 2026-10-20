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
|           landmark, Pengaturan Sistem gear, menu modul lain book (pilihan pemilik)
| - rute  : nama rute tujuan
| - aktif : pola nama rute yang membuat menu tampil aktif (default = rute)
| - sub   : (opsional) submenu .nav-sublink berisi label, rute, aktif (tanpa ikon);
|           menu induknya hanya membuka/menutup submenu sehingga tidak punya rute
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
                'sub' => [
                    [
                        'label' => 'Daftar Portofolio',
                        'rute' => 'admin.portofolio.index',
                        'aktif' => 'admin.portofolio.*',
                    ],
                    [
                        'label' => 'Kategori Portofolio',
                        'rute' => 'admin.kategori-portofolio.index',
                        'aktif' => 'admin.kategori-portofolio.*',
                    ],
                ],
            ],
            [
                'label' => 'Tutorial',
                'ikon' => 'book',
                'sub' => [
                    [
                        'label' => 'Daftar Tutorial',
                        'rute' => 'admin.tutorial.index',
                        'aktif' => 'admin.tutorial.*',
                    ],
                    [
                        'label' => 'Materi Tutorial',
                        'rute' => 'admin.materi-tutorial.index',
                        'aktif' => 'admin.materi-tutorial.*',
                    ],
                    [
                        'label' => 'Kategori Tutorial',
                        'rute' => 'admin.kategori-tutorial.index',
                        'aktif' => 'admin.kategori-tutorial.*',
                    ],
                ],
            ],
            [
                'label' => 'Artikel',
                'ikon' => 'book',
                'sub' => [
                    [
                        'label' => 'Daftar Artikel',
                        'rute' => 'admin.artikel.index',
                        'aktif' => 'admin.artikel.*',
                    ],
                    [
                        'label' => 'Kategori Artikel',
                        'rute' => 'admin.kategori-artikel.index',
                        'aktif' => 'admin.kategori-artikel.*',
                    ],
                ],
            ],
            [
                'label' => 'FAQ',
                'ikon' => 'book',
                'rute' => 'admin.faq.index',
                'aktif' => 'admin.faq.*',
            ],
        ],
    ],
    [
        'judul' => 'Pengaturan Sistem',
        'item' => [
            [
                'label' => 'Identitas Website',
                'ikon' => 'gear',
                'rute' => 'admin.identitas.edit',
                'aktif' => 'admin.identitas.*',
            ],
            [
                'label' => 'Manajemen Modul',
                'ikon' => 'gear',
                'rute' => 'admin.modul-website.index',
                'aktif' => 'admin.modul-website.*',
            ],
            [
                'label' => 'Manajemen Template',
                'ikon' => 'gear',
                'rute' => 'admin.template.index',
                'aktif' => 'admin.template.*',
            ],
        ],
    ],
];
