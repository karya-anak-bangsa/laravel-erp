<?php

namespace App\Services\CompanyProfile;

use App\Models\CompanyProfile\MateriTutorial;
use App\Services\Shared\SlugService;

class MateriTutorialService
{
    public function __construct(private readonly SlugService $slug) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreMateriTutorialRequest; urutan_ke null = setelah materi
     *                                      terakhir tutorialnya
     */
    public function simpan(array $data): MateriTutorial
    {
        $idTutorial = (int) $data['id_tutorial'];

        return MateriTutorial::create([
            ...$data,
            'urutan_ke' => $data['urutan_ke'] ?? MateriTutorial::urutanBerikutnya($idTutorial),
            'slug' => $this->buatSlug($data['judul'], $idTutorial),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi UpdateMateriTutorialRequest (tanpa id_tutorial: materi tidak
     *                                      bisa dipindah ke tutorial lain)
     */
    public function perbarui(MateriTutorial $materiTutorial, array $data): MateriTutorial
    {
        if ($data['judul'] !== $materiTutorial->judul && ! $materiTutorial->slugTerkunci()) {
            $data['slug'] = $this->buatSlug($data['judul'], $materiTutorial->id_tutorial, $materiTutorial);
        }

        $materiTutorial->update($data);

        return $materiTutorial;
    }

    /**
     * Slug dari judul, unik di antara materi tutorial yang sama (termasuk yang terhapus): alamat materi memuat slug
     * tutorialnya, jadi tutorial lain boleh memakai slug yang sama.
     */
    public function buatSlug(string $judul, int $idTutorial, ?MateriTutorial $kecuali = null): string
    {
        return $this->slug->buat($judul, MateriTutorial::withTrashed()->where('id_tutorial', $idTutorial), $kecuali, 'materi');
    }
}
