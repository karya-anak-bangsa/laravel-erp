<?php

namespace App\Http\Requests\Admin\Kas;

use App\Enums\Kas\JenisAkunKas;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreAkunKasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Checkbox tak tercentang tidak terkirim; form juga mengirim hidden input bernilai 0.
            'status_aktif' => $this->boolean('status_aktif'),
        ]);

        // Data bank tidak relevan untuk kas tunai; dikosongkan agar tidak tersimpan sisa isian.
        if ($this->input('jenis_akun') === JenisAkunKas::Tunai->value) {
            $this->merge(['nama_bank' => null, 'nomor_rekening' => null]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|Unique|string>>
     */
    public function rules(): array
    {
        return [
            'nama_akun' => ['required', 'string', 'max:100', $this->aturanNamaAkunUnik()],
            'jenis_akun' => ['required', Rule::enum(JenisAkunKas::class)],
            'nama_bank' => ['nullable', 'required_if:jenis_akun,'.JenisAkunKas::Bank->value, 'string', 'max:100'],
            'nomor_rekening' => ['nullable', 'required_if:jenis_akun,'.JenisAkunKas::Bank->value, 'string', 'max:50'],
            'saldo_awal' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'tanggal_saldo_awal' => ['required', 'date', 'before_or_equal:today'],
            'status_aktif' => ['required', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_akun.unique' => 'Nama akun sudah dipakai akun kas lain.',
            'nama_bank.required_if' => 'Nama bank wajib diisi untuk akun bank.',
            'nomor_rekening.required_if' => 'Nomor rekening wajib diisi untuk akun bank.',
            'tanggal_saldo_awal.before_or_equal' => 'Tanggal saldo awal tidak boleh melewati hari ini.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_akun' => 'nama akun',
            'jenis_akun' => 'jenis akun',
            'nama_bank' => 'nama bank',
            'nomor_rekening' => 'nomor rekening',
            'saldo_awal' => 'saldo awal',
            'tanggal_saldo_awal' => 'tanggal saldo awal',
            'status_aktif' => 'status aktif',
            'keterangan' => 'keterangan',
        ];
    }

    // Akun terhapus (soft delete) tidak menghalangi pemakaian ulang namanya.
    protected function aturanNamaAkunUnik(): Unique
    {
        return Rule::unique('tb_akun_kas', 'nama_akun')->withoutTrashed();
    }
}
