<?php

namespace App\Http\Requests\Admin\Kas;

use App\Enums\Kas\JenisTransaksi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreKategoriTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|Unique|string>>
     */
    public function rules(): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'max:100', $this->aturanNamaKategoriUnik()],
            'jenis_transaksi' => ['required', Rule::enum(JenisTransaksi::class)],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_kategori.unique' => 'Nama kategori sudah dipakai kategori lain dengan jenis yang sama.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_kategori' => 'nama kategori',
            'jenis_transaksi' => 'jenis transaksi',
            'keterangan' => 'keterangan',
        ];
    }

    // Nama boleh sama untuk jenis berbeda (mis. "Lain-lain"); kategori terhapus tidak menghalangi pemakaian ulang.
    protected function aturanNamaKategoriUnik(): Unique
    {
        return Rule::unique('tb_kategori_transaksi', 'nama_kategori')
            ->where('jenis_transaksi', $this->string('jenis_transaksi')->value())
            ->withoutTrashed();
    }
}
