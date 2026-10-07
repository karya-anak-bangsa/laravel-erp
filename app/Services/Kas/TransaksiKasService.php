<?php

namespace App\Services\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;
use App\Services\Shared\FileUploadService;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class TransaksiKasService
{
    // Bukti transaksi adalah dokumen keuangan: disk privat, hanya diakses lewat rute ber-auth.
    public const DISK_BUKTI = 'local';

    public const FOLDER_BUKTI = 'kas/bukti';

    public function __construct(private readonly FileUploadService $fileUpload) {}

    /**
     * @param  array<string, mixed>  $data  hasil validasi (tanpa berkas bukti)
     */
    public function simpan(array $data, ?UploadedFile $bukti, Pengguna $pengguna): TransaksiKas
    {
        $pathBukti = $bukti ? $this->fileUpload->simpan($bukti, self::FOLDER_BUKTI, self::DISK_BUKTI) : null;

        try {
            return DB::transaction(function () use ($data, $pathBukti, $pengguna) {
                // Jenis tidak diambil dari input agar selalu sama dengan jenis kategorinya.
                $jenis = KategoriTransaksi::findOrFail($data['id_kategori_transaksi'])->jenis_transaksi;

                return TransaksiKas::create([
                    ...$data,
                    'jenis_transaksi' => $jenis,
                    'nomor_transaksi' => $this->nomorBerikutnya($jenis, Carbon::parse($data['tanggal_transaksi'])),
                    'bukti_transaksi' => $pathBukti,
                    'created_by' => $pengguna->id_pengguna,
                    'updated_by' => $pengguna->id_pengguna,
                ]);
            });
        } catch (Throwable $e) {
            // Penyimpanan gagal: berkas yang terlanjur diunggah dibuang agar tidak menjadi sampah.
            $this->fileUpload->hapus($pathBukti, self::DISK_BUKTI);

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data  hasil validasi (tanpa berkas bukti)
     */
    public function ubah(TransaksiKas $transaksi, array $data, ?UploadedFile $bukti, bool $hapusBukti, Pengguna $pengguna): TransaksiKas
    {
        $pathLama = $transaksi->bukti_transaksi;
        $pathBaru = $bukti ? $this->fileUpload->simpan($bukti, self::FOLDER_BUKTI, self::DISK_BUKTI) : null;

        try {
            DB::transaction(function () use ($transaksi, $data, $pathBaru, $hapusBukti, $pengguna) {
                $kategori = KategoriTransaksi::findOrFail($data['id_kategori_transaksi']);

                // Awalan nomor (KM/KK) & laporan lama bergantung pada jenis, jadi jenis tidak boleh berubah.
                if ($kategori->jenis_transaksi !== $transaksi->jenis_transaksi) {
                    throw ValidationException::withMessages([
                        'id_kategori_transaksi' => 'Kategori harus berjenis '.mb_strtolower($transaksi->jenis_transaksi->label()).' seperti transaksi semula.',
                    ]);
                }

                // Bulan pada nomor mengikuti tanggal transaksi; pindah bulan berarti nomor baru di bulan itu.
                $tanggal = Carbon::parse($data['tanggal_transaksi']);
                if ($tanggal->format('Ym') !== $transaksi->tanggal_transaksi->format('Ym')) {
                    $transaksi->nomor_transaksi = $this->nomorBerikutnya($transaksi->jenis_transaksi, $tanggal);
                }

                $transaksi->fill($data);
                $transaksi->updated_by = $pengguna->id_pengguna;

                if ($pathBaru !== null) {
                    $transaksi->bukti_transaksi = $pathBaru;
                } elseif ($hapusBukti) {
                    $transaksi->bukti_transaksi = null;
                }

                $transaksi->save();
            });
        } catch (Throwable $e) {
            $this->fileUpload->hapus($pathBaru, self::DISK_BUKTI);

            throw $e;
        }

        // Berkas lama baru dibuang setelah perubahan pasti tersimpan.
        if ($pathLama !== null && $pathLama !== $transaksi->bukti_transaksi) {
            $this->fileUpload->hapus($pathLama, self::DISK_BUKTI);
        }

        return $transaksi;
    }

    // Soft delete; berkas bukti tetap disimpan agar transaksi bisa dipulihkan utuh.
    public function hapus(TransaksiKas $transaksi, Pengguna $pengguna): void
    {
        DB::transaction(function () use ($transaksi, $pengguna) {
            $transaksi->updated_by = $pengguna->id_pengguna;
            $transaksi->save();
            $transaksi->delete();
        });
    }

    /**
     * KM-202610-0001 / KK-202610-0001, berurutan per jenis per bulan tanggal transaksi.
     */
    public function nomorBerikutnya(JenisTransaksi $jenis, CarbonInterface $tanggal): string
    {
        $awalan = $jenis->awalanNomor().'-'.$tanggal->format('Ym').'-';

        // withTrashed karena indeks unik tetap memuat nomor transaksi terhapus. lockForUpdate
        // mengunci rentang nomor bulan itu sampai DB::transaction selesai, sehingga dua
        // penyimpanan bersamaan tidak mendapat nomor yang sama. Urut panjang dulu agar
        // 10000 tetap dianggap lebih besar dari 9999.
        $terakhir = TransaksiKas::withTrashed()
            ->where('nomor_transaksi', 'like', $awalan.'%')
            ->orderByRaw('CHAR_LENGTH(nomor_transaksi) DESC')
            ->orderByDesc('nomor_transaksi')
            ->lockForUpdate()
            ->value('nomor_transaksi');

        $urutan = $terakhir === null ? 1 : (int) substr($terakhir, strlen($awalan)) + 1;

        return $awalan.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
    }
}
