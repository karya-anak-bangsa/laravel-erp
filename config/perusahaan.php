<?php

/*
|--------------------------------------------------------------------------
| Data perusahaan di luar tabel identitas
|--------------------------------------------------------------------------
|
| Nama cadangan dan titik peta kantor di seksi kontak. Titik bawaan = titik tengah Jalan
| Pipit III, Depok Jaya (Nominatim/OpenStreetMap; nomor 146 tidak tercatat di OSM). Bisa
| ditimpa lewat .env tanpa mengubah kode; setelah mengubah .env di produksi jalankan
| php artisan optimize.
|
*/

return [

    // Dipakai halaman error saat tabel identitas tidak terbaca (database bermasalah) dan sebagai judul halaman
    // error panel admin. Nama yang tampil di situs tetap dari Identitas.
    'nama' => 'PT. Teknologi Karya Anak Bangsa',

    // "?:" agar baris .env yang dikosongkan (PETA_LAT=) tetap memakai nilai bawaan, bukan titik 0,0.
    'peta' => [
        'lat' => (float) (env('PETA_LAT') ?: -6.3913047),
        'lng' => (float) (env('PETA_LNG') ?: 106.8060745),
        'zoom' => (int) (env('PETA_ZOOM') ?: 16),
    ],

];
