<?php

namespace App\Http\Requests\Web\CompanyProfile;

use App\Http\Controllers\Web\CompanyProfile\KontakKamiController;
use Illuminate\Foundation\Http\FormRequest;

class StoreKontakKamiRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subjek' => ['required', 'string', 'max:200'],
            'pesan' => ['required', 'string', 'max:5000'],
            // Honeypot: tersembunyi dari pengunjung, biasanya diisi bot.
            'website' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'nama',
            'email' => 'email',
            'subjek' => 'subjek',
            'pesan' => 'pesan',
        ];
    }

    public function dariBot(): bool
    {
        return $this->filled('website');
    }

    // Galat validasi kembali ke formulir di beranda, bukan ke posisi atas halaman.
    protected function getRedirectUrl(): string
    {
        return KontakKamiController::urlFormulir();
    }
}
