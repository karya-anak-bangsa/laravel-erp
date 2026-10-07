<?php

namespace App\Http\Requests\Admin\Kas;

use App\Models\Kas\KategoriTransaksi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateKategoriTransaksiRequest extends StoreKategoriTransaksiRequest
{
    /**
     * @return array<string, array<int, ValidationRule|Unique|string>>
     */
    public function rules(): array
    {
        $aturan = parent::rules();

        // Jenis kategori yang sudah dipakai dikunci agar laporan lama tetap konsisten.
        if ($this->kategoriTransaksi()->transaksiKas()->exists()) {
            $aturan['jenis_transaksi'][] = Rule::in([$this->kategoriTransaksi()->jenis_transaksi->value]);
        }

        return $aturan;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'jenis_transaksi.in' => 'Jenis transaksi tidak dapat diubah karena kategori sudah dipakai transaksi.',
        ];
    }

    protected function aturanNamaKategoriUnik(): Unique
    {
        return parent::aturanNamaKategoriUnik()->ignoreModel($this->kategoriTransaksi());
    }

    private function kategoriTransaksi(): KategoriTransaksi
    {
        /** @var KategoriTransaksi */
        return $this->route('kategoriTransaksi');
    }
}
