<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreKategoriArtikelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|Unique|string>>
     */
    public function rules(): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'max:100', $this->aturanNamaUnik()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_kategori.unique' => 'Nama kategori sudah dipakai kategori lain.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_kategori' => 'nama kategori',
        ];
    }

    // Kategori terhapus (soft delete) tidak menghalangi pemakaian ulang namanya.
    protected function aturanNamaUnik(): Unique
    {
        return Rule::unique('tb_kategori_artikel', 'nama_kategori')->withoutTrashed();
    }
}
