<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\Layanan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreLayananRequest extends FormRequest
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
            'judul' => ['required', 'string', 'max:150', $this->aturanJudulUnik()],
            'deskripsi' => ['required', 'string', 'max:1000'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'urutan_ke' => ['required', 'integer', 'min:0', 'max:'.Layanan::URUTAN_MAKS],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.unique' => 'Judul layanan sudah dipakai layanan lain.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul',
            'deskripsi' => 'deskripsi',
            'keterangan' => 'keterangan',
            'gambar' => 'gambar',
            'urutan_ke' => 'urutan',
        ];
    }

    // Layanan terhapus (soft delete) tidak menghalangi pemakaian ulang judulnya.
    protected function aturanJudulUnik(): Unique
    {
        return Rule::unique('tb_layanan', 'judul')->withoutTrashed();
    }
}
