<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Http\Requests\Concerns\MembersihkanHtml;
use App\Rules\PanjangTeksHtml;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class StorePortofolioRequest extends FormRequest
{
    use MembersihkanHtml;

    // Gambar per portofolio termasuk gambar utama (pilihan pemilik 2026-10-18), jadi gambar tambahan paling banyak 4.
    public const MAKS_GAMBAR = 5;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->bersihkanHtml(['deskripsi']);
    }

    /**
     * @return array<string, array<int, ValidationRule|Exists|string>>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:200'],
            'id_kategori_portofolio' => ['required', 'integer', Rule::exists('tb_kategori_portofolio', 'id_kategori_portofolio')->withoutTrashed()],
            'deskripsi' => ['required', 'string', new PanjangTeksHtml(5000)],
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'galeri' => ['nullable', 'array', 'max:'.(self::MAKS_GAMBAR - 1)],
            // Baris repeater yang belum diberi berkas terkirim kosong (null) dan dilewati FileUploadService.
            'galeri.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'galeri.max' => self::pesanBatasGambar(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul',
            'id_kategori_portofolio' => 'kategori',
            'deskripsi' => 'deskripsi',
            'gambar' => 'gambar utama',
            'galeri' => 'gambar tambahan',
            'galeri.*' => 'gambar tambahan',
        ];
    }

    /**
     * Pesan bila gambar tambahan melewati batas, juga saat mengubah (gambar lama ikut dihitung).
     */
    protected static function pesanBatasGambar(): string
    {
        return 'Setiap portofolio paling banyak '.self::MAKS_GAMBAR.' gambar termasuk gambar utama, jadi gambar tambahan paling banyak '.(self::MAKS_GAMBAR - 1).'.';
    }
}
