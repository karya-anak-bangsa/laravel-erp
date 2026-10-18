<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\Portofolio;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePortofolioRequest extends StorePortofolioRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Form mengirim gambar tambahan lama yang dipertahankan; bila semua barisnya dibuang, isian ini tidak terkirim
        // sama sekali sehingga artinya "tidak ada yang dipertahankan", bukan "tidak diubah".
        $this->mergeIfMissing(['galeri_lama' => []]);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            // Saat ubah, gambar lama dipakai bila tidak ada unggahan baru.
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // Hanya gambar tambahan milik portofolio ini yang bisa dipertahankan.
            'galeri_lama' => ['present', 'array'],
            'galeri_lama.*' => ['integer', 'distinct', Rule::exists('tb_gambar_portofolio', 'id_gambar_portofolio')
                ->where('id_portofolio', $this->portofolioDiubah()->id_portofolio)],
        ];
    }

    /**
     * Gambar tambahan lama yang dipertahankan ditambah unggahan baru tetap dalam batas.
     *
     * @return list<Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                // Unggahan baru saja sudah melewati batas (aturan galeri.max), jadi pesannya tidak perlu diulang.
                if ($validator->errors()->has('galeri')) {
                    return;
                }

                $dipertahankan = array_filter(Arr::wrap($this->input('galeri_lama')), is_numeric(...));
                $lama = $this->portofolioDiubah()->gambarPortofolio()->whereKey($dipertahankan)->count();
                // Baris repeater tanpa berkas bernilai null, jadi tidak dihitung.
                $baru = count(array_filter(Arr::wrap($validator->getData()['galeri'] ?? [])));

                if ($lama + $baru > self::MAKS_GAMBAR - 1) {
                    $validator->errors()->add('galeri', self::pesanBatasGambar());
                }
            },
        ];
    }

    private function portofolioDiubah(): Portofolio
    {
        /** @var Portofolio $portofolio */
        $portofolio = $this->route('portofolio');

        return $portofolio;
    }
}
