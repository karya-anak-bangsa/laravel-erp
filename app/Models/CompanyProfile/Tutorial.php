<?php

namespace App\Models\CompanyProfile;

use App\Enums\CompanyProfile\StatusPublikasi;
use Database\Factories\CompanyProfile\TutorialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Tutorial extends Model
{
    /** @use HasFactory<TutorialFactory> */
    use HasFactory, SoftDeletes;

    public const DISK = 'public';

    public const FOLDER = 'company-profile/tutorial';

    protected $table = 'tb_tutorial';

    protected $primaryKey = 'id_tutorial';

    protected $fillable = ['id_kategori_tutorial', 'judul', 'slug', 'deskripsi', 'gambar', 'status_publikasi'];

    protected function casts(): array
    {
        return [
            'status_publikasi' => StatusPublikasi::class,
        ];
    }

    /**
     * Terbaru lebih dulu (id memecah created_at kembar, mis. data seeder yang dibuat dalam detik yang sama).
     *
     * @param  Builder<Tutorial>  $query
     */
    public function scopeTerbaru(Builder $query): void
    {
        $query->latest()->latest('id_tutorial');
    }

    /**
     * Tutorial yang tampil di frontend: terbit dan punya minimal satu materi.
     *
     * @param  Builder<Tutorial>  $query
     */
    public function scopeTayang(Builder $query): void
    {
        $query->where('status_publikasi', StatusPublikasi::Terbit)->has('materiTutorial');
    }

    /**
     * Rute /tutorial/{tutorial:slug}/{materiTutorial:slug} mencari materi lewat relasi materiTutorial(); bawaan Laravel
     * menebak bentuk jamak bahasa Inggris (materiTutorials).
     *
     * @param  string  $childType
     */
    protected function childRouteBindingRelationshipName($childType): string
    {
        return Str::camel($childType);
    }

    /**
     * @return BelongsTo<KategoriTutorial, $this>
     */
    public function kategoriTutorial(): BelongsTo
    {
        return $this->belongsTo(KategoriTutorial::class, 'id_kategori_tutorial', 'id_kategori_tutorial');
    }

    /**
     * Materi berurutan seperti tampil di website: urutan_ke, lalu urutan dibuat untuk angka yang sama.
     *
     * @return HasMany<MateriTutorial, $this>
     */
    public function materiTutorial(): HasMany
    {
        return $this->hasMany(MateriTutorial::class, 'id_tutorial', 'id_tutorial')->berurutan();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(self::DISK)->url($this->gambar));
    }
}
