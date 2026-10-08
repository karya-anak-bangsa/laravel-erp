<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\KategoriArtikel;
use Illuminate\Validation\Rules\Unique;

class UpdateKategoriArtikelRequest extends StoreKategoriArtikelRequest
{
    protected function aturanNamaUnik(): Unique
    {
        /** @var KategoriArtikel $kategoriArtikel */
        $kategoriArtikel = $this->route('kategori_artikel');

        return parent::aturanNamaUnik()->ignoreModel($kategoriArtikel);
    }
}
