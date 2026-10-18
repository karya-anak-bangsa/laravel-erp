<?php

namespace App\Http\Requests\Admin\CompanyProfile;

use App\Models\CompanyProfile\Portofolio;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePortofolioRequest extends StorePortofolioRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            // Saat ubah, gambar lama dipakai bila tidak ada unggahan baru.
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // Hanya gambar tambahan milik portofolio ini yang boleh dibuang.
            'hapus_galeri' => ['nullable', 'array'],
            'hapus_galeri.*' => ['integer', Rule::exists('tb_gambar_portofolio', 'id_gambar_portofolio')
                ->where('id_portofolio', $this->portofolioDiubah()->id_portofolio)],
        ];
    }

    /**
     * Gambar tambahan yang tersisa setelah dibuang, ditambah unggahan baru, tetap dalam batas.
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

                $dibuang = array_filter((array) $this->input('hapus_galeri', []), is_numeric(...));
                $sisa = $this->portofolioDiubah()->gambarPortofolio()->whereKeyNot($dibuang)->count();

                if ($sisa + count(Arr::wrap($this->file('galeri'))) > self::MAKS_GAMBAR - 1) {
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
