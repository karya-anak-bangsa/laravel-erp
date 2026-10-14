<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Enums\CompanyProfile\JenisTemplate;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\CompanyProfile\BerandaController;
use App\Http\Requests\Admin\CompanyProfile\PratinjauTemplateRequest;
use App\Http\Requests\Admin\CompanyProfile\StoreTemplateRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateTemplateRequest;
use App\Models\CompanyProfile\Template;
use App\Services\CompanyProfile\TemaService;
use App\Services\CompanyProfile\TemplateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $jenis = JenisTemplate::tryFrom($request->string('jenis')->value());
        $status = $request->string('status')->value();

        $template = Template::query()
            ->when($q !== '', fn (Builder $query) => $query->where('nama', 'like', "%{$q}%"))
            ->when($jenis, fn (Builder $query) => $query->where('jenis', $jenis))
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn (Builder $query) => $query->where('status_aktif', $status === 'aktif'))
            // Template yang sedang dipakai website selalu di atas; sisanya per jenis lalu abjad.
            ->orderByDesc('status_aktif')
            ->orderBy('jenis')
            ->orderBy('nama')
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.template.index', compact('template'));
    }

    public function create(TemaService $temaService): View
    {
        $warna = $temaService->templateAktif(JenisTemplate::FullColor);
        $mono = $temaService->templateAktif(JenisTemplate::Monochrome);

        return view('admin.company-profile.template.create', [
            // Isian awal menyalin template yang sedang aktif agar admin cukup mengubah yang perlu; nonaktif agar
            // website tidak berubah tanpa sengaja.
            'template' => new Template([
                'jenis' => JenisTemplate::FullColor,
                'warna_utama' => $warna['warna_utama'],
                'warna_aksen' => $warna['warna_aksen'],
                'nada_dasar' => $mono['nada_dasar'],
                'font' => $warna['font'],
                'sudut' => $warna['sudut'],
                'skala' => $warna['skala'],
                'lebar_konten' => $warna['lebar_konten'],
                'ketebalan_judul' => $warna['ketebalan_judul'],
                'bayangan' => $warna['bayangan'],
                'status_aktif' => false,
            ]),
        ]);
    }

    public function store(StoreTemplateRequest $request, TemplateService $templateService): RedirectResponse
    {
        $templateService->simpan($request->validated());

        return redirect()->route('admin.template.index')->with('success', 'Template berhasil ditambahkan.');
    }

    public function edit(Template $template): View
    {
        return view('admin.company-profile.template.edit', compact('template'));
    }

    public function update(UpdateTemplateRequest $request, Template $template, TemplateService $templateService): RedirectResponse
    {
        $templateService->perbarui($template, $request->validated());

        return redirect()->route('admin.template.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(Template $template): RedirectResponse
    {
        // Website harus selalu punya satu template aktif per jenis (DATABASE.md).
        if ($template->status_aktif) {
            return redirect()->route('admin.template.index')->with('error',
                "Template “{$template->nama}” sedang aktif. Aktifkan template {$template->jenis->label()} lain lebih dulu.");
        }

        $template->delete();

        return redirect()->route('admin.template.index')->with('success', 'Template berhasil dihapus.');
    }

    /**
     * Beranda asli dengan nilai template ini (termasuk isian form yang belum disimpan), dibuka di tab baru.
     * Halaman publik terpisah dari panel admin karena CSS Basecoat dan Gentelella bertabrakan.
     */
    public function pratinjau(PratinjauTemplateRequest $request, BerandaController $beranda): View
    {
        // Dibaca TemaComposer dari request asli; FormRequest hanyalah salinannya, jadi atributnya tidak ikut terbaca.
        // Tidak ada yang disimpan.
        request()->attributes->set(TemaService::PRATINJAU, new Template($request->validated()));

        return $beranda();
    }
}
