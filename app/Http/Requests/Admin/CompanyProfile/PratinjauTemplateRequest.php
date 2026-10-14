<?php

namespace App\Http\Requests\Admin\CompanyProfile;

class PratinjauTemplateRequest extends StoreTemplateRequest
{
    /**
     * Pratinjau tidak menyimpan apa pun: nama hanya untuk pita penanda dan boleh kosong (template baru).
     */
    public function rules(): array
    {
        return [
            'nama' => ['nullable', 'string', 'max:100'],
            ...$this->aturanTampilan(),
        ];
    }
}
