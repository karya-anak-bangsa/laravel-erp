<?php

namespace App\Http\Requests\Admin\CompanyProfile;

class UpdateArtikelRequest extends StoreArtikelRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            // Saat ubah, gambar lama dipakai bila tidak ada unggahan baru.
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
