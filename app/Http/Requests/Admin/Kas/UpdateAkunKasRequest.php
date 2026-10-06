<?php

namespace App\Http\Requests\Admin\Kas;

use App\Models\Kas\AkunKas;
use Illuminate\Validation\Rules\Unique;

class UpdateAkunKasRequest extends StoreAkunKasRequest
{
    protected function aturanNamaAkunUnik(): Unique
    {
        /** @var AkunKas $akunKas */
        $akunKas = $this->route('akunKas');

        return parent::aturanNamaAkunUnik()->ignoreModel($akunKas);
    }
}
