<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\UpdateIdentitasRequest;
use App\Models\CompanyProfile\Identitas;
use App\Services\CompanyProfile\IdentitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IdentitasController extends Controller
{
    public function edit(): View
    {
        return view('admin.company-profile.identitas.edit', ['identitas' => Identitas::tunggal()]);
    }

    public function update(UpdateIdentitasRequest $request, IdentitasService $identitasService): RedirectResponse
    {
        $identitasService->perbarui(Identitas::tunggal(), $request->validated());

        return redirect()->route('admin.identitas.edit')->with('success', 'Identitas perusahaan berhasil diperbarui.');
    }
}
