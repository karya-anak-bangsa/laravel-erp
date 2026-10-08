<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Enums\CompanyProfile\GayaCta;
use App\Http\Requests\Concerns\MembersihkanHtml;
use App\Rules\PanjangTeksHtml;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreHeroRequest extends FormRequest
{
    use MembersihkanHtml;

    public const MAKS_KEYWORD = 10;

    public const MAKS_CTA = 3;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->bersihkanHtml(['deskripsi']);

        $keyword = $this->input('keyword');
        $cta = $this->input('cta');

        $this->merge([
            // Checkbox tak tercentang tidak terkirim; form juga mengirim hidden input bernilai 0.
            'status_aktif' => $this->boolean('status_aktif'),
            // Baris repeater yang dibiarkan kosong dibuang; indeks diurutkan ulang agar
            // pesan error (keyword.0, cta.1.url) cocok dengan baris yang tampil.
            'keyword' => is_array($keyword) ? array_values(array_filter($keyword, fn ($kata) => filled($kata))) : [],
            'cta' => is_array($cta)
                ? array_values(array_filter($cta, fn ($tombol) => is_array($tombol) && (filled($tombol['label'] ?? null) || filled($tombol['url'] ?? null))))
                : [],
        ]);
    }

    /**
     * @return array<string, array<int, ValidationRule|Enum|string>>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', new PanjangTeksHtml(1000)],
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'keyword' => ['required', 'array', 'min:1', 'max:'.self::MAKS_KEYWORD],
            'keyword.*' => ['required', 'string', 'max:50', 'distinct:ignore_case'],
            'cta' => ['present', 'array', 'max:'.self::MAKS_CTA],
            'cta.*.label' => ['required', 'string', 'max:30'],
            // Hanya tautan halaman (/), anchor (#), web, email, dan telepon; skema lain
            // seperti javascript: ditolak karena URL ini dirender sebagai href di frontend.
            'cta.*.url' => ['required', 'string', 'max:255', 'regex:/^(\/(?!\/)|#|https?:\/\/|mailto:|tel:)\S*$/i'],
            'cta.*.gaya' => ['required', Rule::enum(GayaCta::class)],
            'status_aktif' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'keyword.required' => 'Isi minimal satu keyword.',
            'keyword.max' => 'Keyword maksimal :max buah.',
            'keyword.*.distinct' => 'Keyword tidak boleh berulang.',
            'cta.max' => 'Tombol CTA maksimal :max buah.',
            'cta.*.url.regex' => 'URL tombol harus diawali /, #, https://, mailto:, atau tel: dan tanpa spasi.',
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
            'gambar' => 'gambar',
            'keyword' => 'keyword',
            'keyword.*' => 'keyword',
            'cta' => 'tombol CTA',
            'cta.*.label' => 'label tombol',
            'cta.*.url' => 'URL tombol',
            'cta.*.gaya' => 'gaya tombol',
            'status_aktif' => 'status aktif',
        ];
    }
}
