<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\KategoriPortofolio;
use Illuminate\Validation\Rules\Unique;

class UpdateKategoriPortofolioRequest extends StoreKategoriPortofolioRequest
{
    protected function aturanNamaUnik(): Unique
    {
        /** @var KategoriPortofolio $kategoriPortofolio */
        $kategoriPortofolio = $this->route('kategori_portofolio');

        return parent::aturanNamaUnik()->ignoreModel($kategoriPortofolio);
    }
}
