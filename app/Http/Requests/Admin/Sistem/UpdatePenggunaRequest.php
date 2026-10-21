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
        return parent::aturanEmailUnik()->ignoreModel($this->penggunaDiubah());
    }

    // Kosong = kata sandi lama dipertahankan, jadi isiannya dibuang dari data tervalidasi.
    protected function aturanPassword(): array
    {
        return [Rule::excludeIf($this->isNotFilled('password')), 'string', Password::min(8), 'confirmed'];
    }

    protected function penggunaDiubah(): Pengguna
    {
        /** @var Pengguna $pengguna */
        $pengguna = $this->route('pengguna');

        return $pengguna;
    }
}
