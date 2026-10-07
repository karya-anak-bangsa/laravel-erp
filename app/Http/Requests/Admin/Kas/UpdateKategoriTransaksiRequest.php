<?php

namespace App\Http\Requests\Admin\Kas;

use App\Models\Kas\KategoriTransaksi;
use Illuminate\Validation\Rules\Unique;

class UpdateKategoriTransaksiRequest extends StoreKategoriTransaksiRequest
{
    protected function aturanNamaKategoriUnik(): Unique
    {
        /** @var KategoriTransaksi $kategoriTransaksi */
        $kategoriTransaksi = $this->route('kategoriTransaksi');

        return parent::aturanNamaKategoriUnik()->ignoreModel($kategoriTransaksi);
    }
}
