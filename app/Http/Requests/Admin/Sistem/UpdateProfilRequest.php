<?php

namespace App\Http\Requests\Admin\Sistem;

use App\Models\Pengguna;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\ExcludeIf;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\Rules\Unique;

class UpdateProfilRequest extends UpdatePenggunaRequest
{
    /**
     * @return array<string, array<int, ExcludeIf|Password|RequiredIf|Unique|string>>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            // Email dipakai login, jadi menggantinya juga meminta kata sandi agar sesi yang dibajak tidak bisa
            // mengambil alih akun.
            'password_saat_ini' => [
                Rule::requiredIf(fn () => $this->filled('password') || $this->input('email') !== $this->penggunaDiubah()->email),
                'nullable',
                'string',
                'current_password',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'password_saat_ini.required' => 'Masukkan kata sandi saat ini untuk mengganti email atau kata sandi.',
            'password_saat_ini.current_password' => 'Kata sandi saat ini salah.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'password' => 'kata sandi baru',
            'password_saat_ini' => 'kata sandi saat ini',
        ];
    }

    protected function penggunaDiubah(): Pengguna
    {
        /** @var Pengguna $pengguna */
        $pengguna = $this->user();

        return $pengguna;
    }
}
