<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\KategoriTutorial;
use Illuminate\Validation\Rules\Unique;

class UpdateKategoriTutorialRequest extends StoreKategoriTutorialRequest
{
    protected function aturanNamaUnik(): Unique
    {
        /** @var KategoriTutorial $kategoriTutorial */
        $kategoriTutorial = $this->route('kategori_tutorial');

        return parent::aturanNamaUnik()->ignoreModel($kategoriTutorial);
    }
}
