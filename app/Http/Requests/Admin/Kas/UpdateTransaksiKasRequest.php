<?php

namespace App\Http\Requests\Admin\Kas;

use App\Models\Kas\TransaksiKas;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class UpdateTransaksiKasRequest extends StoreTransaksiKasRequest
{
    protected function prepareForValidation(): void
    {
        // Switch tak tercentang tidak terkirim; form juga mengirim hidden input bernilai 0.
        $this->merge(['hapus_bukti' => $this->boolean('hapus_bukti')]);
    }

    /**
     * @return array<string, array<int, ValidationRule|Exists|string>>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'hapus_bukti' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'id_kategori_transaksi.exists' => 'Kategori harus berjenis '.mb_strtolower($this->transaksiKas()->jenis_transaksi->label()).' seperti transaksi semula.',
        ];
    }

    // Akun yang sudah dipakai transaksi ini tetap sah walau kini nonaktif.
    protected function aturanAkunKas(): Exists
    {
        if ($this->integer('id_akun_kas') === $this->transaksiKas()->id_akun_kas) {
            return Rule::exists('tb_akun_kas', 'id_akun_kas');
        }

        return parent::aturanAkunKas();
    }

    // Jenis transaksi tidak boleh berubah (awalan nomor KM/KK & laporan lama bergantung padanya).
    protected function aturanKategoriTransaksi(): Exists
    {
        return parent::aturanKategoriTransaksi()->where('jenis_transaksi', $this->transaksiKas()->jenis_transaksi->value);
    }

    private function transaksiKas(): TransaksiKas
    {
        /** @var TransaksiKas */
        return $this->route('transaksiKas');
    }
}
