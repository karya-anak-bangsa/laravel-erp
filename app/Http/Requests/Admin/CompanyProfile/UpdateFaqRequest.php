<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\Faq;
use Illuminate\Validation\Rules\Unique;

class UpdateFaqRequest extends StoreFaqRequest
{
    protected function aturanPertanyaanUnik(): Unique
    {
        /** @var Faq $faq */
        $faq = $this->route('faq');

        return parent::aturanPertanyaanUnik()->ignoreModel($faq);
    }
}
