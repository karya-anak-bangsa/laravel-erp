<?php

namespace App\Http\Requests\Admin\Kas;

use App\Models\Kas\AkunKas;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Validator;

class StoreTransaksiKasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|Exists|string>>
     */
    public function rules(): array
    {
        return [
            'tanggal_transaksi' => ['required', 'date', 'before_or_equal:today'],
            'id_akun_kas' => ['required', 'integer', $this->aturanAkunKas()],
            'id_kategori_transaksi' => ['required', 'integer', $this->aturanKategoriTransaksi()],
            'jumlah' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999999.99'],
            'nama_pihak' => ['nullable', 'string', 'max:150'],
            'keterangan' => ['required', 'string', 'max:1000'],
            'bukti_transaksi' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            // Transaksi sebelum tanggal saldo awal akan membuat saldo awal tidak bermakna.
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['tanggal_transaksi', 'id_akun_kas'])) {
                    return;
                }

                $akun = AkunKas::withTrashed()->find($this->integer('id_akun_kas'));
                $tanggal = Carbon::parse($this->string('tanggal_transaksi')->value());

                if ($akun && $tanggal->lt($akun->tanggal_saldo_awal)) {
                    $validator->errors()->add(
                        'tanggal_transaksi',
                        'Tanggal transaksi tidak boleh sebelum tanggal saldo awal akun ('.$akun->tanggal_saldo_awal->translatedFormat('d F Y').').',
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal_transaksi.before_or_equal' => 'Tanggal transaksi tidak boleh melewati hari ini.',
            'id_akun_kas.exists' => 'Akun kas yang dipilih tidak valid atau sudah nonaktif.',
            'jumlah.gt' => 'Jumlah harus lebih dari 0.',
            'bukti_transaksi.mimes' => 'Bukti transaksi harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'bukti_transaksi.max' => 'Ukuran bukti transaksi maksimal 5 MB.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tanggal_transaksi' => 'tanggal transaksi',
            'id_akun_kas' => 'akun kas',
            'id_kategori_transaksi' => 'kategori transaksi',
            'jumlah' => 'jumlah',
            'nama_pihak' => 'nama pihak',
            'keterangan' => 'keterangan',
            'bukti_transaksi' => 'bukti transaksi',
        ];
    }

    // Transaksi baru hanya boleh memakai akun yang aktif.
    protected function aturanAkunKas(): Exists
    {
        return Rule::exists('tb_akun_kas', 'id_akun_kas')->where('status_aktif', true)->withoutTrashed();
    }

    protected function aturanKategoriTransaksi(): Exists
    {
        return Rule::exists('tb_kategori_transaksi', 'id_kategori_transaksi')->withoutTrashed();
    }
}
