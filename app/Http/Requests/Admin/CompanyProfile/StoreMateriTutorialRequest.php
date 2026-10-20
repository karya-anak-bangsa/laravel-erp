<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Http\Requests\Concerns\MembersihkanHtml;
use App\Models\CompanyProfile\MateriTutorial;
use App\Rules\PanjangTeksHtml;
use App\Services\Shared\HtmlSanitizerService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class StoreMateriTutorialRequest extends FormRequest
{
    use MembersihkanHtml;

    // Sama dengan batas isi artikel (±5.000 kata).
    public const PANJANG_ISI_MAKS = StoreArtikelRequest::PANJANG_ISI_MAKS;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->bersihkanHtml(['deskripsi'], judulBagian: true, kode: true);
    }

    /**
     * @return array<string, array<int, ValidationRule|Exists|string>>
     */
    public function rules(): array
    {
        return [
            'id_tutorial' => ['required', 'integer', Rule::exists('tb_tutorial', 'id_tutorial')->withoutTrashed()],
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', new PanjangTeksHtml(self::PANJANG_ISI_MAKS, HtmlSanitizerService::PANJANG_HTML_ARTIKEL_MAKS)],
            // Kosong = setelah materi terakhir tutorialnya (MateriTutorialService).
            'urutan_ke' => ['nullable', 'integer', 'min:0', 'max:'.MateriTutorial::URUTAN_MAKS],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'id_tutorial' => 'tutorial',
            'judul' => 'judul',
            'deskripsi' => 'isi materi',
            'urutan_ke' => 'urutan',
        ];
    }
}
