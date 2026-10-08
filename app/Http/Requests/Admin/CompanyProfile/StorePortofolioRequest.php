<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Http\Requests\Concerns\MembersihkanHtml;
use App\Rules\PanjangTeksHtml;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePortofolioRequest extends FormRequest
{
    use MembersihkanHtml;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->bersihkanHtml(['deskripsi']);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'kategori' => ['required', 'string', 'max:50'],
            'deskripsi' => ['required', 'string', new PanjangTeksHtml(5000)],
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
