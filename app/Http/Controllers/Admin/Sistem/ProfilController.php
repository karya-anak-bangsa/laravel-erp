<?php

namespace App\Http\Controllers\Admin\Sistem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sistem\UpdateProfilRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.sistem.profil.edit', ['pengguna' => $request->user()]);
    }

    public function update(UpdateProfilRequest $request): RedirectResponse
    {
        // Instance milik guard yang diubah, agar auth.session menyimpan hash kata sandi baru di sesi ini dan
        // hanya sesi di perangkat lain yang berakhir.
        $request->user()->update($request->validated());

        return redirect()->route('admin.profil.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
