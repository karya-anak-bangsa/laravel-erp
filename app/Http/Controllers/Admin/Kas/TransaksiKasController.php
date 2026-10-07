<?php

namespace App\Http\Controllers\Admin\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Kas\StoreTransaksiKasRequest;
use App\Http\Requests\Admin\Kas\UpdateTransaksiKasRequest;
use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;
use App\Services\Kas\TransaksiKasService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiKasController extends Controller
{
    public function __construct(private readonly TransaksiKasService $transaksiKasService) {}

    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $jenis = JenisTransaksi::tryFrom($request->string('jenis')->value());
        $akun = $request->integer('akun');
        $kategori = $request->integer('kategori');
        $dari = $this->tanggalFilter($request, 'dari');
        $sampai = $this->tanggalFilter($request, 'sampai');

        $transaksiKas = TransaksiKas::query()
            ->with(['akunKas', 'kategoriTransaksi'])
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('nomor_transaksi', 'like', "%{$q}%")
                ->orWhere('nama_pihak', 'like', "%{$q}%")
                ->orWhere('keterangan', 'like', "%{$q}%")))
            ->when($jenis, fn (Builder $query) => $query->where('jenis_transaksi', $jenis))
            ->when($akun > 0, fn (Builder $query) => $query->where('id_akun_kas', $akun))
            ->when($kategori > 0, fn (Builder $query) => $query->where('id_kategori_transaksi', $kategori))
            ->when($dari, fn (Builder $query) => $query->where('tanggal_transaksi', '>=', $dari))
            ->when($sampai, fn (Builder $query) => $query->where('tanggal_transaksi', '<=', $sampai))
            ->latest('tanggal_transaksi')
            ->latest('id_transaksi_kas')
            ->paginate(25)
            ->withQueryString();

        return view('admin.kas.transaksi-kas.index', [
            'transaksiKas' => $transaksiKas,
            'opsiAkun' => AkunKas::query()->orderBy('nama_akun')->pluck('nama_akun', 'id_akun_kas'),
            'opsiKategori' => KategoriTransaksi::opsiPerJenis(),
        ]);
    }

    public function create(): View
    {
        return view('admin.kas.transaksi-kas.create', [
            'transaksiKas' => new TransaksiKas(['tanggal_transaksi' => today()]),
            'opsiAkun' => $this->opsiAkun(),
            'opsiKategori' => KategoriTransaksi::opsiPerJenis(),
        ]);
    }

    public function store(StoreTransaksiKasRequest $request): RedirectResponse
    {
        $transaksi = $this->transaksiKasService->simpan(
            $request->safe()->except('bukti_transaksi'),
            $request->file('bukti_transaksi'),
            $this->pengguna($request),
        );

        return redirect()->route('admin.transaksi-kas.index')
            ->with('success', "Transaksi {$transaksi->nomor_transaksi} berhasil dicatat.");
    }

    public function show(TransaksiKas $transaksiKas): View
    {
        $transaksiKas->load(['akunKas', 'kategoriTransaksi', 'pembuat', 'pengubah']);

        return view('admin.kas.transaksi-kas.show', compact('transaksiKas'));
    }

    public function edit(TransaksiKas $transaksiKas): View
    {
        return view('admin.kas.transaksi-kas.edit', [
            'transaksiKas' => $transaksiKas,
            'opsiAkun' => $this->opsiAkun($transaksiKas),
            // Jenis transaksi terkunci, jadi hanya kategori sejenis yang ditawarkan.
            'opsiKategori' => KategoriTransaksi::opsiPerJenis($transaksiKas->jenis_transaksi),
        ]);
    }

    public function update(UpdateTransaksiKasRequest $request, TransaksiKas $transaksiKas): RedirectResponse
    {
        $this->transaksiKasService->ubah(
            $transaksiKas,
            $request->safe()->except(['bukti_transaksi', 'hapus_bukti']),
            $request->file('bukti_transaksi'),
            $request->boolean('hapus_bukti'),
            $this->pengguna($request),
        );

        return redirect()->route('admin.transaksi-kas.index')
            ->with('success', "Transaksi {$transaksiKas->nomor_transaksi} berhasil diperbarui.");
    }

    public function destroy(Request $request, TransaksiKas $transaksiKas): RedirectResponse
    {
        $this->transaksiKasService->hapus($transaksiKas, $this->pengguna($request));

        return redirect()->route('admin.transaksi-kas.index')
            ->with('success', "Transaksi {$transaksiKas->nomor_transaksi} berhasil dihapus.");
    }

    // Bukti disimpan di disk privat; hanya bisa dibuka lewat rute ini (di bawah middleware auth).
    public function bukti(TransaksiKas $transaksiKas): StreamedResponse
    {
        $disk = Storage::disk(TransaksiKasService::DISK_BUKTI);
        $path = $transaksiKas->bukti_transaksi;

        abort_if($path === null || ! $disk->exists($path), 404);

        return $disk->response(
            $path,
            'bukti-'.$transaksiKas->nomor_transaksi.'.'.pathinfo($path, PATHINFO_EXTENSION),
            ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'],
        );
    }

    /**
     * Akun aktif; saat ubah, akun milik transaksi tetap ditawarkan walau kini nonaktif.
     *
     * @return array<int, string>
     */
    private function opsiAkun(?TransaksiKas $transaksiKas = null): array
    {
        return AkunKas::query()
            ->where(fn (Builder $query) => $query
                ->where('status_aktif', true)
                ->when($transaksiKas, fn (Builder $query) => $query->orWhere('id_akun_kas', $transaksiKas?->id_akun_kas)))
            ->orderBy('nama_akun')
            ->pluck('nama_akun', 'id_akun_kas')
            ->all();
    }

    // Filter tanggal dari query string; format selain Y-m-d diabaikan, bukan error 500.
    private function tanggalFilter(Request $request, string $kunci): ?string
    {
        $nilai = $request->string($kunci)->value();

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai) === 1 && strtotime($nilai) !== false ? $nilai : null;
    }

    private function pengguna(Request $request): Pengguna
    {
        /** @var Pengguna */
        return $request->user();
    }
}
