<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use App\Http\Requests\Concerns\MembersihkanHtml;
use App\Rules\PanjangTeksHtml;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;

class StoreTutorialRequest extends FormRequest
{
    use MembersihkanHtml;

    // Pengantar singkat; isi pelajarannya ada di materi.
    public const PANJANG_DESKRIPSI_MAKS = 2000;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->bersihkanHtml(['deskripsi']);
    }

    /**
     * @return array<string, array<int, ValidationRule|Enum|Exists|string>>
     */
    public function rules(): array
    {
        return [
            'id_kategori_tutorial' => ['required', 'integer', Rule::exists('tb_kategori_tutorial', 'id_kategori_tutorial')->withoutTrashed()],
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', new PanjangTeksHtml(self::PANJANG_DESKRIPSI_MAKS)],
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status_publikasi' => ['required', Rule::enum(StatusPublikasi::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'id_kategori_tutorial' => 'kategori',
            'judul' => 'judul',
            'deskripsi' => 'deskripsi',
            'gambar' => 'gambar sampul',
            'status_publikasi' => 'status',
        ];
    }
}
