<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\Layanan;
use Illuminate\Validation\Rules\Unique;

class UpdateLayananRequest extends StoreLayananRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            // Saat ubah, gambar lama dipakai bila tidak ada unggahan baru.
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function aturanJudulUnik(): Unique
    {
        /** @var Layanan $layanan */
        $layanan = $this->route('layanan');

        return parent::aturanJudulUnik()->ignoreModel($layanan);
    }
}
