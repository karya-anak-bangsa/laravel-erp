<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\PortofolioFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Portofolio extends Model
{
    /** @use HasFactory<PortofolioFactory> */
    use HasFactory, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/portofolio';

    /**
     * Kategori layanan utama, sekaligus urutan kartu portofolio di beranda (keputusan pemilik 2026-10-11).
     */
    public const KATEGORI_UTAMA = ['Website', 'Mobile Apps', 'Pelatihan IT', 'Sertifikasi IT', 'Bootcamp'];

    protected $table = 'tb_portofolio';

    protected $primaryKey = 'id_portofolio';

    protected $fillable = ['judul', 'slug', 'deskripsi', 'gambar', 'kategori'];

    /**
     * Kategori yang sedang dipakai, untuk saran isian dan filter daftar.
     *
     * @return list<string>
     */
    public static function daftarKategori(): array
    {
        return static::query()->distinct()->orderBy('kategori')->pluck('kategori')->all();
    }

    /**
     * Kunci urut kategori untuk sortBy(): kategori utama menurut KATEGORI_UTAMA, kategori lain sesudahnya
     * menurut abjad. Dicocokkan lewat slug karena kategori ditulis bebas (mis. "pelatihan it").
     *
     * @return array{int, string}
     */
    public static function kunciUrutKategori(string $kategori): array
    {
        $slug = Str::slug($kategori);
        $posisi = array_search($slug, array_map(Str::slug(...), self::KATEGORI_UTAMA), true);

        return $posisi === false ? [count(self::KATEGORI_UTAMA), $slug] : [$posisi, ''];
    }

    /**
     * Terbaru lebih dulu (id memecah created_at kembar, mis. data seeder yang dibuat dalam detik yang sama).
     *
     * @param  Builder<Portofolio>  $query
     */
    public function scopeTerbaru(Builder $query): void
    {
        $query->latest()->latest('id_portofolio');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->gambar));
    }
}
