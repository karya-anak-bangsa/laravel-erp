<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePortofolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'kategori' => ['required', 'string', 'max:50'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul',
            'kategori' => 'kategori',
            'deskripsi' => 'deskripsi',
            'gambar' => 'gambar',
        ];
    }
}
