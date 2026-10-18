<?php

namespace App\Models\CompanyProfile;

use Database\Factories\CompanyProfile\GambarPortofolioFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Gambar tambahan portofolio untuk carousel detail. Tanpa soft delete: gambar yang dibuang lewat form dihapus
 * permanen beserta berkasnya, sama seperti gambar utama yang diganti.
 */
class GambarPortofolio extends Model
{
    /** @use HasFactory<GambarPortofolioFactory> */
    use HasFactory;

    protected $table = 'tb_gambar_portofolio';

    protected $primaryKey = 'id_gambar_portofolio';

    protected $fillable = ['id_portofolio', 'gambar'];

    /**
     * @return BelongsTo<Portofolio, $this>
     */
    public function portofolio(): BelongsTo
    {
        return $this->belongsTo(Portofolio::class, 'id_portofolio', 'id_portofolio');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function gambarUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk(Portofolio::DISK)->url($this->gambar));
    }
}
