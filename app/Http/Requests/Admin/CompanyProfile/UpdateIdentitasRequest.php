<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdentitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $gmap = $this->input('link_gmap');

        // Google Maps memberi kode <iframe> utuh saat "Sematkan peta"; yang disimpan cukup URL src-nya.
        if (is_string($gmap) && preg_match('/<iframe[^>]*\ssrc=["\']([^"\']+)["\']/i', $gmap, $cocok) === 1) {
            $this->merge(['link_gmap' => html_entity_decode($cocok[1])]);
        }
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
            // Aturan image tidak menerima .ico, padahal format itu lazim untuk favicon.
            'favicon_website' => ['nullable', 'file', 'mimes:png,ico,webp', 'max:512'],
            'email' => ['required', 'email', 'max:150'],
            'telepon' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9 \-]*$/'],
            'alamat' => ['required', 'string', 'max:1000'],
            'link_gmap' => ['nullable', 'url:https', 'starts_with:https://www.google.com/maps/embed', 'max:2000'],
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
            'telepon.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda hubung, dan awalan +.',
            'link_gmap.starts_with' => 'Link Google Maps harus berupa URL sematan (diawali https://www.google.com/maps/embed).',
            'favicon_website.mimes' => 'Favicon harus berupa file bertipe PNG, ICO, atau WEBP.',
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
            'telepon' => 'telepon',
            'alamat' => 'alamat',
            'link_gmap' => 'link Google Maps',
            'link_youtube' => 'link YouTube',
            'link_instagram' => 'link Instagram',
            'link_whatsapp' => 'link WhatsApp',
        ];
    }
}
