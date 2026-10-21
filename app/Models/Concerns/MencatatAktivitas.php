<?php

namespace App\Models\Concerns;

use App\Enums\Sistem\AksiAktivitas;
use App\Services\Sistem\LogAktivitasService;

/**
 * Mencatat tambah, ubah, hapus, dan aktifkan kembali data ini di Log Aktivitas (Pengaturan Sistem). Model pemakainya
 * menyebut nama jenis datanya (konstanta LABEL_LOG), label isian yang dicatat saat diubah (ISIAN_LOG, urut dan
 * berlabel seperti form), dan nama datanya (namaUntukLog()), lalu didaftarkan di morph map AppServiceProvider.
 */
trait MencatatAktivitas
{
    /**
     * Isian di luar kolom tabel yang berubah pada penyimpanan berikutnya (tandaiBerubah()).
     *
     * @var list<string>
     */
    private array $isianLainBerubah = [];

    /**
     * Nama data saat aksi terjadi, mis. judul artikel; tetap terbaca di log walau data kemudian diganti nama.
     */
    abstract public function namaUntukLog(): string;

    public static function bootMencatatAktivitas(): void
    {
        static::created(fn (self $model) => $model->catatAktivitas(AksiAktivitas::Tambah));
        // Kolom di luar ISIAN_LOG (slug, timestamp, deleted_at saat diaktifkan kembali) tidak dicatat.
        static::updated(fn (self $model) => $model->catatUbah(array_keys($model->getChanges())));
        // Isian lain yang berubah tanpa ada kolom tabel yang berubah tidak memicu updated.
        static::saved(fn (self $model) => $model->catatUbah());
        static::deleted(fn (self $model) => $model->catatAktivitas(AksiAktivitas::Hapus));
        static::registerModelEvent('restored', fn (self $model) => $model->catatAktivitas(AksiAktivitas::AktifkanKembali));
    }

    /**
     * Menandai isian di luar kolom tabel (mis. gambar tambahan portofolio) yang ikut berubah pada penyimpanan
     * berikutnya. updated_at ikut diperbarui karena datanya dianggap berubah.
     */
    public function tandaiBerubah(string $isian): void
    {
        $this->isianLainBerubah[] = $isian;
        $this->updateTimestamps();
    }

    /**
     * @param  list<string>  $isian  label isian yang berubah (aksi Ubah)
     */
    private function catatAktivitas(AksiAktivitas $aksi, array $isian = []): void
    {
        app(LogAktivitasService::class)->catatData($aksi, $this, $this->namaUntukLog(), $isian);
    }

    /**
     * @param  list<string>  $kolom  kolom tabel yang berubah
     */
    private function catatUbah(array $kolom = []): void
    {
        $berubah = array_flip([...$kolom, ...$this->isianLainBerubah]);
        $this->isianLainBerubah = [];
        // Urut ISIAN_LOG, sama dengan urutan isian di form.
        $isian = array_values(array_intersect_key(static::ISIAN_LOG, $berubah));

        if ($isian !== []) {
            $this->catatAktivitas(AksiAktivitas::Ubah, $isian);
        }
    }
}
