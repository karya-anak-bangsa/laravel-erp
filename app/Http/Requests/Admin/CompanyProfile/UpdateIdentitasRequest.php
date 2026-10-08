<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIdentitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'link_youtube' => 'link YouTube',
            'link_instagram' => 'link Instagram',
            'link_whatsapp' => 'link WhatsApp',
        ];
    }
}
