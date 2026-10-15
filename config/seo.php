<?php

/*
|--------------------------------------------------------------------------
| Mesin pencari
|--------------------------------------------------------------------------
|
| SEO_INDEKS=false menandai semua halaman publik noindex dan menghapus sitemap dari robots.txt.
| Dipakai selama produksi masih berisi data contoh (tahap uji, CLAUDE.md §1), lalu dibuka saat
| data real masuk. Bawaannya true agar situs tidak tersembunyi dari mesin pencari hanya karena
| baris .env lupa diisi. Setelah mengubah .env di produksi jalankan php artisan optimize.
|
*/

return [

    'indeks' => (bool) env('SEO_INDEKS', true),

];
