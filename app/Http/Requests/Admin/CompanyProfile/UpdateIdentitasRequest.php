<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdentitasRequest extends FormRequest
{
    // Batas masing-masing email dan telepon agar kolom Kontak di footer tetap ringkas.
    public const MAKS_KONTAK = 3;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Baris repeater yang dibiarkan kosong dibuang; indeks diurutkan ulang agar pesan error (email.1) cocok
        // dengan baris yang tampil.
        $this->merge([
            'email' => $this->daftarTerisi('email'),
            'telepon' => $this->daftarTerisi('telepon'),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama_perusahaan' => ['required', 'string', 'max:150'],
            'judul_website' => ['required', 'string', 'max:150'],
            'alamat_website' => ['required', 'url:http,https', 'max:255'],
            'meta_deskripsi' => ['nullable', 'string', 'max:255'],
            'meta_keyword' => ['nullable', 'string', 'max:255'],
            'logo_website' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // Aturan image tidak menerima .ico, padahal format itu lazim untuk favicon. Tanpa WEBP: Google Search tidak
            // menampilkan favicon WEBP di hasil pencarian.
            'favicon_website' => ['nullable', 'file', 'mimes:png,ico', 'max:512'],
            'email' => ['required', 'array', 'max:'.self::MAKS_KONTAK],
            'email.*' => ['required', 'string', 'email', 'max:150', 'distinct:ignore_case'],
            'telepon' => ['required', 'array', 'max:'.self::MAKS_KONTAK],
            'telepon.*' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9 \-]*$/', 'distinct'],
            'alamat' => ['required', 'string', 'max:1000'],
            'link_youtube' => ['nullable', 'url:http,https', 'max:255'],
            'link_instagram' => ['nullable', 'url:http,https', 'max:255'],
            'link_whatsapp' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Isi minimal satu email.',
            'email.max' => 'Email maksimal :max alamat.',
            'email.*.distinct' => 'Email tidak boleh berulang.',
            'telepon.required' => 'Isi minimal satu nomor telepon.',
            'telepon.max' => 'Telepon maksimal :max nomor.',
            'telepon.*.distinct' => 'Nomor telepon tidak boleh berulang.',
            'telepon.*.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda hubung, dan awalan +.',
            'favicon_website.mimes' => 'Favicon harus berupa file bertipe PNG atau ICO.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_perusahaan' => 'nama perusahaan',
            'judul_website' => 'judul website',
            'alamat_website' => 'alamat website',
            'meta_deskripsi' => 'meta deskripsi',
            'meta_keyword' => 'meta keyword',
            'logo_website' => 'logo',
            'favicon_website' => 'favicon',
            'email' => 'email',
            'email.*' => 'email',
            'telepon' => 'telepon',
            'telepon.*' => 'telepon',
            'alamat' => 'alamat',
            'link_youtube' => 'link YouTube',
            'link_instagram' => 'link Instagram',
            'link_whatsapp' => 'link WhatsApp',
        ];
    }

    /**
     * @return list<mixed>
     */
    private function daftarTerisi(string $kunci): array
    {
        $daftar = $this->input($kunci);

        return is_array($daftar) ? array_values(array_filter($daftar, fn ($nilai) => filled($nilai))) : [];
    }
}
