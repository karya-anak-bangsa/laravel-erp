<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Enums\CompanyProfile\PengaturanTampilan;
use Illuminate\Validation\Rule;

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
            // Tautan pratinjau di modal Lihat membawa nilai tersimpan tanpa pilihan Pengaturan (dianggap Custom).
            'pengaturan' => ['nullable', Rule::enum(PengaturanTampilan::class)],
        ];
    }
}
