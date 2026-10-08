<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreLayananRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateLayananRequest;
use App\Models\CompanyProfile\Layanan;
use App\Services\CompanyProfile\LayananService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LayananController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();

        $layanan = Layanan::query()
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('judul', 'like', "%{$q}%")
                ->orWhere('deskripsi', 'like', "%{$q}%")))
            // Daftar mengikuti urutan tampil di website agar admin melihat susunan yang sama.
            ->berurutan()
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.layanan.index', compact('layanan'));
    }

    public function create(): View
    {
        return view('admin.company-profile.layanan.create', [
            'layanan' => new Layanan(['urutan_ke' => Layanan::urutanBerikutnya()]),
        ]);
    }

    public function store(StoreLayananRequest $request, LayananService $layananService): RedirectResponse
    {
        $layananService->simpan($request->validated());

        return redirect()->route('admin.layanan.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Layanan $layanan): View
    {
        return view('admin.company-profile.layanan.edit', compact('layanan'));
    }

    public function update(UpdateLayananRequest $request, Layanan $layanan, LayananService $layananService): RedirectResponse
    {
        $layananService->perbarui($layanan, $request->validated());

        return redirect()->route('admin.layanan.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Layanan $layanan): RedirectResponse
    {
        $layanan->delete();

        return redirect()->route('admin.layanan.index')->with('success', 'Layanan berhasil dihapus.');
    }
}
