<?php

namespace App\Http\Controllers\Admin\Kas;

use App\Enums\Kas\JenisAkunKas;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Kas\StoreAkunKasRequest;
use App\Http\Requests\Admin\Kas\UpdateAkunKasRequest;
use App\Models\Kas\AkunKas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkunKasController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $jenis = JenisAkunKas::tryFrom($request->string('jenis')->value());
        $status = $request->string('status')->value();

        $akunKas = AkunKas::query()
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('nama_akun', 'like', "%{$q}%")
                ->orWhere('nama_bank', 'like', "%{$q}%")
                ->orWhere('nomor_rekening', 'like', "%{$q}%")))
            ->when($jenis, fn (Builder $query) => $query->where('jenis_akun', $jenis))
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn (Builder $query) => $query->where('status_aktif', $status === 'aktif'))
            // Akun kas jumlahnya sedikit & dicari berdasarkan nama, jadi diurutkan abjad, bukan terbaru.
            ->orderBy('nama_akun')
            ->paginate(25)
            ->withQueryString();

        return view('admin.kas.akun-kas.index', compact('akunKas'));
    }

    public function create(): View
    {
        return view('admin.kas.akun-kas.create', [
            'akunKas' => new AkunKas(['status_aktif' => true, 'tanggal_saldo_awal' => today()]),
        ]);
    }

    public function store(StoreAkunKasRequest $request): RedirectResponse
    {
        AkunKas::create($request->validated());

        return redirect()->route('admin.akun-kas.index')->with('success', 'Akun kas berhasil ditambahkan.');
    }

    public function edit(AkunKas $akunKas): View
    {
        return view('admin.kas.akun-kas.edit', compact('akunKas'));
    }

    public function update(UpdateAkunKasRequest $request, AkunKas $akunKas): RedirectResponse
    {
        $akunKas->update($request->validated());

        return redirect()->route('admin.akun-kas.index')->with('success', 'Akun kas berhasil diperbarui.');
    }

    public function destroy(AkunKas $akunKas): RedirectResponse
    {
        // Saldo & laporan bergantung pada akun ini; akun yang tak dipakai cukup dinonaktifkan.
        if ($akunKas->transaksiKas()->exists()) {
            return redirect()->route('admin.akun-kas.index')
                ->with('error', "Akun “{$akunKas->nama_akun}” tidak dapat dihapus karena sudah memiliki transaksi. Nonaktifkan akun bila tidak dipakai lagi.");
        }

        $akunKas->delete();

        return redirect()->route('admin.akun-kas.index')->with('success', 'Akun kas berhasil dihapus.');
    }
}
