<?php

namespace App\Enums\Concerns;

/**
 * Pilihan [nilai => label] untuk <x-admin.form-select>. Dipakai backed enum yang punya label().
 */
trait MenyediakanOpsi
{
    /**
     * @return array<string, string>
     */
    public static function opsi(): array
    {
        $opsi = [];

        foreach (self::cases() as $kasus) {
            $opsi[$kasus->value] = $kasus->label();
        }

        return $opsi;
    }
}
