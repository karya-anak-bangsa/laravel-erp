<?php

/*
|--------------------------------------------------------------------------
| Data perusahaan di luar tabel identitas
|--------------------------------------------------------------------------
|
| Titik peta kantor di seksi kontak. Bawaan = titik tengah Jalan Pipit III, Depok Jaya
| (Nominatim/OpenStreetMap; nomor 146 tidak tercatat di OSM). Bisa ditimpa lewat .env
| tanpa mengubah kode; setelah mengubah .env di produksi jalankan php artisan optimize.
|
*/

return [

    // "?:" agar baris .env yang dikosongkan (PETA_LAT=) tetap memakai nilai bawaan, bukan titik 0,0.
    'peta' => [
        'lat' => (float) (env('PETA_LAT') ?: -6.3913047),
        'lng' => (float) (env('PETA_LNG') ?: 106.8060745),
        'zoom' => (int) (env('PETA_ZOOM') ?: 16),
    ],

];
