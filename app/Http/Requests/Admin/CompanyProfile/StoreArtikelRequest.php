<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Requests\Concerns\MembersihkanHtml;
use App\Rules\PanjangTeksHtml;
use App\Services\Shared\HtmlSanitizerService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;

class StoreArtikelRequest extends FormRequest
{
    use MembersihkanHtml;

    // Batas teks terlihat isi artikel (±5.000 kata).
    public const PANJANG_ISI_MAKS = 30000;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->bersihkanHtml(['deskripsi'], judulBagian: true);
    }

    /**
     * @return array<string, array<int, ValidationRule|Exists|Enum|string>>
     */
    public function rules(): array
    {
        return [
            'id_kategori_artikel' => ['required', 'integer', Rule::exists('tb_kategori_artikel', 'id_kategori_artikel')->withoutTrashed()],
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', new PanjangTeksHtml(self::PANJANG_ISI_MAKS, HtmlSanitizerService::PANJANG_HTML_ARTIKEL_MAKS)],
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'status_publikasi' => ['required', Rule::enum(StatusPublikasi::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'id_kategori_artikel' => 'kategori',
            'judul' => 'judul',
            'deskripsi' => 'isi artikel',
            'gambar' => 'gambar',
            'tanggal' => 'tanggal',
            'status_publikasi' => 'status',
        ];
    }
}
