<?php

namespace App\Models\Sistem;

use App\Enums\Sistem\AksiAktivitas;
use App\Models\Pengguna;
use Database\Factories\Sistem\LogAktivitasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

/**
 * Satu kejadian di Log Aktivitas, ditulis LogAktivitasService. Tanpa updated_at dan soft delete: baris log tidak pernah
 * diubah, dan dihapus permanen setelah masa simpannya.
 */
class LogAktivitas extends Model
{
    /** @use HasFactory<LogAktivitasFactory> */
    use HasFactory;

    // Log yang lebih lama dihapus permanen agar tidak menumpuk (pilihan pemilik 2026-10-21).
    public const MASA_SIMPAN_HARI = 7;

    public const UPDATED_AT = null;

    protected $table = 'tb_log_aktivitas';

    protected $primaryKey = 'id_log_aktivitas';

    protected $fillable = ['id_pengguna', 'aksi', 'subjek_type', 'subjek_id', 'label_subjek', 'perubahan', 'alamat_ip'];

    protected function casts(): array
    {
        return [
            'aksi' => AksiAktivitas::class,
            // Daftar label isian yang berubah (aksi Ubah).
            'perubahan' => 'array',
        ];
    }

    /**
     * Nama jenis data dari alias morph map, mis. kategori_artikel → "Kategori Artikel" (LABEL_LOG modelnya). Alias tanpa
     * model (mis. modul yang kelak dihapus) tetap terbaca dari aliasnya.
     */
    public static function labelJenis(string $alias): string
    {
        $kelas = Relation::getMorphedModel($alias);

        return $kelas !== null && defined($kelas.'::LABEL_LOG') ? constant($kelas.'::LABEL_LOG') : Str::headline($alias);
    }

    /**
     * Pelaku; akun Nonaktif (soft delete) tetap tercantum.
     *
     * @return BelongsTo<Pengguna, $this>
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna')->withTrashed();
    }

    /**
     * Nama jenis data, mis. "Artikel"; null untuk masuk & keluar.
     *
     * @return Attribute<string|null, never>
     */
    protected function jenisData(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->subjek_type === null ? null : self::labelJenis($this->subjek_type));
    }
}
