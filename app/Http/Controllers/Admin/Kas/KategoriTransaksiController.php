<?php

namespace App\Http\Controllers\Admin\Kas;

use App\Enums\Kas\JenisTransaksi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Kas\StoreKategoriTransaksiRequest;
use App\Http\Requests\Admin\Kas\UpdateKategoriTransaksiRequest;
use App\Models\Kas\KategoriTransaksi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriTransaksiController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $jenis = JenisTransaksi::tryFrom($request->string('jenis')->value());

        $kategoriTransaksi = KategoriTransaksi::query()
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('nama_kategori', 'like', "%{$q}%")
                ->orWhere('keterangan', 'like', "%{$q}%")))
            ->when($jenis, fn (Builder $query) => $query->where('jenis_transaksi', $jenis))
            // Data master: dikelompokkan per jenis (pemasukan dulu) lalu abjad, bukan terbaru.
            ->orderBy('jenis_transaksi')
            ->orderBy('nama_kategori')
            ->paginate(25)
            ->withQueryString();

        return view('admin.kas.kategori-transaksi.index', compact('kategoriTransaksi'));
    }

    public function create(Request $request): View
    {
        return view('admin.kas.kategori-transaksi.create', [
            // Jenis diisi otomatis dari filter daftar agar tidak perlu dipilih ulang.
            'kategoriTransaksi' => new KategoriTransaksi([
                'jenis_transaksi' => JenisTransaksi::tryFrom($request->string('jenis')->value()),
            ]),
        ]);
    }

    public function store(StoreKategoriTransaksiRequest $request): RedirectResponse
    {
        KategoriTransaksi::create($request->validated());

        return redirect()->route('admin.kategori-transaksi.index')->with('success', 'Kategori transaksi berhasil ditambahkan.');
    }

    public function edit(KategoriTransaksi $kategoriTransaksi): View
    {
        return view('admin.kas.kategori-transaksi.edit', compact('kategoriTransaksi'));
    }

    public function update(UpdateKategoriTransaksiRequest $request, KategoriTransaksi $kategoriTransaksi): RedirectResponse
    {
        $kategoriTransaksi->update($request->validated());

        return redirect()->route('admin.kategori-transaksi.index')->with('success', 'Kategori transaksi berhasil diperbarui.');
    }

    public function destroy(KategoriTransaksi $kategoriTransaksi): RedirectResponse
    {
        $kategoriTransaksi->delete();

        return redirect()->route('admin.kategori-transaksi.index')->with('success', 'Kategori transaksi berhasil dihapus.');
    }
}
