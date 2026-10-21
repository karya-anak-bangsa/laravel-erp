<?php

namespace App\Http\Requests\Admin\Sistem;

use App\Enums\Sistem\PeranPengguna;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\ExcludeIf;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class StorePenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, Enum|ExcludeIf|Password|Unique|string>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', $this->aturanEmailUnik()],
            'peran' => ['required', Rule::enum(PeranPengguna::class)],
            'password' => $this->aturanPassword(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah dipakai akun lain, termasuk akun yang sudah dihapus.',
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
            'peran' => 'peran',
            'password' => 'kata sandi',
        ];
    }

    // Tanpa withoutTrashed(): email akun terhapus tetap terkunci karena kolomnya UNIQUE di database (pilihan pemilik).
    protected function aturanEmailUnik(): Unique
    {
        return Rule::unique('tb_pengguna', 'email');
    }

    /**
     * @return array<int, ExcludeIf|Password|string>
     */
    protected function aturanPassword(): array
    {
        return ['required', 'string', Password::min(8), 'confirmed'];
    }
}
