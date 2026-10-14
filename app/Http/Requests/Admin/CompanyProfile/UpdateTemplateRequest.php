<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\Template;
use Illuminate\Validation\Rules\Unique;

class UpdateTemplateRequest extends StoreTemplateRequest
{
    protected function prepareForValidation(): void
    {
        // Template aktif tidak bisa dinonaktifkan langsung atau diganti jenisnya, agar website selalu punya satu
        // template per jenis (DATABASE.md). Isian itu dikunci di form; nilainya ditegaskan di sini juga.
        if ($this->templateDiubah()->status_aktif) {
            $this->merge(['jenis' => $this->templateDiubah()->jenis->value, 'status_aktif' => '1']);
        }

        parent::prepareForValidation();
    }

    protected function aturanNamaUnik(): Unique
    {
        return parent::aturanNamaUnik()->ignoreModel($this->templateDiubah());
    }

    private function templateDiubah(): Template
    {
        /** @var Template $template */
        $template = $this->route('template');

        return $template;
    }
}
