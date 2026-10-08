<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Http\Requests\Concerns\MembersihkanHtml;
use App\Models\CompanyProfile\Faq;
use App\Rules\PanjangTeksHtml;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreFaqRequest extends FormRequest
{
    use MembersihkanHtml;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->bersihkanHtml(['jawaban']);
    }

    /**
     * @return array<string, array<int, ValidationRule|Unique|string>>
     */
    public function rules(): array
    {
        return [
            'pertanyaan' => ['required', 'string', 'max:255', $this->aturanPertanyaanUnik()],
            'jawaban' => ['required', 'string', new PanjangTeksHtml(2000)],
            'urutan_ke' => ['required', 'integer', 'min:0', 'max:'.Faq::URUTAN_MAKS],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pertanyaan.unique' => 'Pertanyaan ini sudah ada di FAQ lain.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pertanyaan' => 'pertanyaan',
            'jawaban' => 'jawaban',
            'urutan_ke' => 'urutan',
        ];
    }

    // FAQ terhapus (soft delete) tidak menghalangi pemakaian ulang pertanyaannya.
    protected function aturanPertanyaanUnik(): Unique
    {
        return Rule::unique('tb_faq', 'pertanyaan')->withoutTrashed();
    }
}
