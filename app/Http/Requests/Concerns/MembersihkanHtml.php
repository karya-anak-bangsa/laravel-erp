<?php

namespace App\Http\Requests\Concerns;

use App\Services\Shared\HtmlSanitizerService;

/**
 * Untuk Form Request berisi isian editor WYSIWYG: HTML disanitasi sebelum divalidasi,
 * sehingga data validated() yang disimpan sudah aman ditampilkan dengan {!! !!}.
 */
trait MembersihkanHtml
{
    /**
     * @param  list<string>  $kolom
     */
    protected function bersihkanHtml(array $kolom): void
    {
        $sanitizer = app(HtmlSanitizerService::class);
        $bersih = [];

        foreach ($kolom as $nama) {
            $nilai = $this->input($nama);

            // Isi kosong menjadi null, sama seperti middleware ConvertEmptyStringsToNull.
            if (is_string($nilai)) {
                $bersih[$nama] = $sanitizer->bersihkan($nilai) ?: null;
            }
        }

        $this->merge($bersih);
    }
}
