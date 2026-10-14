<?php

use App\Enums\CompanyProfile\FontTemplate;
use Illuminate\Support\Str;

// @fonts dengan alias yang tidak ada di manifest menggagalkan halaman publik, jadi setiap font yang bisa dipilih
// admin wajib dibangun Vite. Test feature tidak bisa menangkapnya karena memakai manifest font kosong (Pest.php).
it('membangun setiap font template di vite.config.js sampai bobot 800', function (FontTemplate $font) {
    $vite = (string) file_get_contents(dirname(__DIR__, 3).'/vite.config.js');

    expect($vite)->toMatch('/bunny\(\''.preg_quote($font->family(), '/').'\', \{\s+weights: \[400, 500, 600, 700, 800\]/')
        ->and($font->alias())->toBe(Str::slug($font->family()));
})->with(FontTemplate::cases());
