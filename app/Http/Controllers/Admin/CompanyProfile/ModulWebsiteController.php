<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\UpdateModulWebsiteRequest;
use App\Models\CompanyProfile\ModulWebsite;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModulWebsiteController extends Controller
{
    public function index(): View
    {
        // Baris tetap, satu per JenisModul (ModulWebsiteSeeder), jadi tanpa pencarian & paginasi. Urutannya = urutan
        // menu dan seksi beranda di website.
        $modulWebsite = ModulWebsite::query()->berurutan()->get();

        return view('admin.company-profile.modul-website.index', compact('modulWebsite'));
    }

    public function edit(ModulWebsite $modulWebsite): View
    {
        return view('admin.company-profile.modul-website.edit', compact('modulWebsite'));
    }

    public function update(UpdateModulWebsiteRequest $request, ModulWebsite $modulWebsite): RedirectResponse
    {
        $modulWebsite->update($request->validated());

        $label = $modulWebsite->jenis->label();

        return redirect()->route('admin.modul-website.index')->with('success', $modulWebsite->status_aktif
            ? "Modul {$label} berhasil diperbarui dan tampil di website."
            : "Modul {$label} berhasil diperbarui dan disembunyikan dari website.");
    }
}
