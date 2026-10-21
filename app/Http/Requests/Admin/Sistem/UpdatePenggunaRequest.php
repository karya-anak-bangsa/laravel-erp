<?php

namespace App\Http\Requests\Admin\Sistem;

use App\Models\Pengguna;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class UpdatePenggunaRequest extends StorePenggunaRequest
{
    protected function aturanEmailUnik(): Unique
    {
        /** @var Pengguna $pengguna */
        $pengguna = $this->route('pengguna');

        return parent::aturanEmailUnik()->ignoreModel($pengguna);
    }

    // Kosong = kata sandi lama dipertahankan, jadi isiannya dibuang dari data tervalidasi.
    protected function aturanPassword(): array
    {
        return [Rule::excludeIf($this->isNotFilled('password')), 'string', Password::min(8), 'confirmed'];
    }
}
