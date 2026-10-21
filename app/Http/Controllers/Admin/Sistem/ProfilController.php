<?php

namespace App\Http\Controllers\Admin\Sistem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sistem\UpdateProfilRequest;
use App\Models\Pengguna;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(#[CurrentUser] Pengguna $pengguna): View
    {
        return view('admin.sistem.profil.edit', ['pengguna' => $pengguna]);
    }

    public function update(UpdateProfilRequest $request, #[CurrentUser] Pengguna $pengguna): RedirectResponse
    {
        // Instance milik guard yang diubah, agar auth.session menyimpan hash kata sandi baru di sesi ini dan
        // hanya sesi di perangkat lain yang berakhir.
        $pengguna->update($request->validated());

        return redirect()->route('admin.dashboard')->with('success', 'Profil berhasil diperbarui.');
    }
}
