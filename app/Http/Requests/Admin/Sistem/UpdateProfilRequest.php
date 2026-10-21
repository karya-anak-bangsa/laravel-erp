<?php

namespace App\Http\Requests\Admin\Sistem;

use App\Models\Pengguna;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\ExcludeIf;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class UpdateProfilRequest extends UpdatePenggunaRequest
{
    /**
     * @return array<string, array<int, Enum|ExcludeIf|Password|Unique|string>>
     */
    public function rules(): array
    {
        // Tanpa peran: tidak ada yang bisa menaikkan atau menurunkan perannya sendiri, dan kiriman peran ikut
        // terbuang dari data tervalidasi.
        return Arr::except(parent::rules(), 'peran');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'password' => 'kata sandi baru',
        ];
    }

    protected function penggunaDiubah(): Pengguna
    {
        /** @var Pengguna $pengguna */
        $pengguna = $this->user();

        return $pengguna;
    }
}
