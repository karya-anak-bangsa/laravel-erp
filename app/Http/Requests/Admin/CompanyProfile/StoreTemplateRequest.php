<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Enums\CompanyProfile\BayanganTemplate;
use App\Enums\CompanyProfile\FontTemplate;
use App\Enums\CompanyProfile\JenisTemplate;
use App\Enums\CompanyProfile\KetebalanJudul;
use App\Enums\CompanyProfile\LebarKonten;
use App\Enums\CompanyProfile\NadaDasar;
use App\Enums\CompanyProfile\SkalaTemplate;
use App\Enums\CompanyProfile\SudutTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Unique;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Checkbox tak tercentang tidak terkirim; form juga mengirim hidden input bernilai 0.
            'status_aktif' => $this->boolean('status_aktif'),
            // Hex disimpan huruf kecil, sama dengan nilai pemilih warna browser.
            'warna_utama' => $this->hexKecil('warna_utama'),
            'warna_aksen' => $this->hexKecil('warna_aksen'),
        ]);

        // Kolom yang tidak relevan dengan jenisnya disimpan NULL (DATABASE.md); form tetap mengirim keduanya.
        $jenis = JenisTemplate::tryFrom((string) $this->input('jenis'));

        if ($jenis === JenisTemplate::FullColor) {
            $this->merge(['nada_dasar' => null]);
        } elseif ($jenis === JenisTemplate::Monochrome) {
            $this->merge(['warna_utama' => null, 'warna_aksen' => null]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|Enum|Unique|string>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100', $this->aturanNamaUnik()],
            ...$this->aturanTampilan(),
            'status_aktif' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.unique' => 'Nama template sudah dipakai template lain.',
            'warna_utama.required_if' => 'Warna utama wajib diisi untuk template Full Color.',
            'warna_aksen.required_if' => 'Warna aksen wajib diisi untuk template Full Color.',
            'warna_utama.regex' => 'Warna utama harus berformat #rrggbb, mis. #15253f.',
            'warna_aksen.regex' => 'Warna aksen harus berformat #rrggbb, mis. #cb1839.',
            'nada_dasar.required_if' => 'Nada dasar wajib dipilih untuk template Monochrome.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'nama template',
            'jenis' => 'jenis',
            'warna_utama' => 'warna utama',
            'warna_aksen' => 'warna aksen',
            'nada_dasar' => 'nada dasar',
            'font' => 'font',
            'sudut' => 'sudut',
            'skala' => 'skala',
            'lebar_konten' => 'lebar konten',
            'ketebalan_judul' => 'ketebalan judul',
            'bayangan' => 'bayangan',
            'status_aktif' => 'status aktif',
        ];
    }

    /**
     * Aturan nilai tampilan; dipakai juga PratinjauTemplateRequest.
     *
     * @return array<string, array<int, Enum|string>>
     */
    protected function aturanTampilan(): array
    {
        // Hanya #rrggbb: nilai ini ditulis ke <style> frontend (TemaService).
        $hex = 'regex:/^#[0-9a-f]{6}$/';

        return [
            'jenis' => ['required', Rule::enum(JenisTemplate::class)],
            'warna_utama' => ['nullable', 'required_if:jenis,'.JenisTemplate::FullColor->value, 'string', $hex],
            'warna_aksen' => ['nullable', 'required_if:jenis,'.JenisTemplate::FullColor->value, 'string', $hex],
            'nada_dasar' => ['nullable', 'required_if:jenis,'.JenisTemplate::Monochrome->value, Rule::enum(NadaDasar::class)],
            'font' => ['required', Rule::enum(FontTemplate::class)],
            'sudut' => ['required', Rule::enum(SudutTemplate::class)],
            'skala' => ['required', Rule::enum(SkalaTemplate::class)],
            'lebar_konten' => ['required', Rule::enum(LebarKonten::class)],
            'ketebalan_judul' => ['required', Rule::enum(KetebalanJudul::class)],
            'bayangan' => ['required', Rule::enum(BayanganTemplate::class)],
        ];
    }

    // Template terhapus (soft delete) tidak menghalangi pemakaian ulang namanya.
    protected function aturanNamaUnik(): Unique
    {
        return Rule::unique('tb_template', 'nama')->withoutTrashed();
    }

    private function hexKecil(string $kunci): mixed
    {
        $nilai = $this->input($kunci);

        return is_string($nilai) ? strtolower($nilai) : $nilai;
    }
}
