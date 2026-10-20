<?php

namespace App\Http\Controllers\Admin\CompanyProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyProfile\StoreMateriTutorialRequest;
use App\Http\Requests\Admin\CompanyProfile\UpdateMateriTutorialRequest;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\MateriTutorialService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MateriTutorialController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->value();
        $idTutorial = $request->integer('tutorial');

        $materiTutorial = MateriTutorial::query()
            ->with('tutorial:id_tutorial,judul')
            ->when($q !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('judul', 'like', "%{$q}%")
                ->orWhere('deskripsi', 'like', "%{$q}%")))
            ->when($idTutorial > 0, fn (Builder $query) => $query->where('id_tutorial', $idTutorial))
            // Dikelompokkan per tutorial (terbaru di atas), lalu urutan tampil materinya.
            ->orderByDesc('id_tutorial')
            ->berurutan()
            ->paginate(25)
            ->withQueryString();

        return view('admin.company-profile.materi-tutorial.index', [
            'materiTutorial' => $materiTutorial,
            'opsiTutorial' => $this->opsiTutorial(),
        ]);
    }

    /**
     * ?tutorial= (dari daftar materi yang sedang disaring) langsung memilih tutorialnya beserta urutan berikutnya.
     */
    public function create(Request $request): View
    {
        $opsiTutorial = $this->opsiTutorial();
        $idTutorial = $request->integer('tutorial');

        return view('admin.company-profile.materi-tutorial.create', [
            'materiTutorial' => array_key_exists($idTutorial, $opsiTutorial)
                ? new MateriTutorial(['id_tutorial' => $idTutorial, 'urutan_ke' => MateriTutorial::urutanBerikutnya($idTutorial)])
                : new MateriTutorial,
            'opsiTutorial' => $opsiTutorial,
        ]);
    }

    public function store(StoreMateriTutorialRequest $request, MateriTutorialService $materiTutorialService): RedirectResponse
    {
        $materiTutorial = $materiTutorialService->simpan($request->validated());

        return redirect()->route('admin.materi-tutorial.index', ['tutorial' => $materiTutorial->id_tutorial])
            ->with('success', 'Materi tutorial berhasil ditambahkan.');
    }

    public function edit(MateriTutorial $materiTutorial): View
    {
        return view('admin.company-profile.materi-tutorial.edit', [
            'materiTutorial' => $materiTutorial,
            'opsiTutorial' => $this->opsiTutorial(),
        ]);
    }

    public function update(UpdateMateriTutorialRequest $request, MateriTutorial $materiTutorial, MateriTutorialService $materiTutorialService): RedirectResponse
    {
        $materiTutorialService->perbarui($materiTutorial, $request->validated());

        return redirect()->route('admin.materi-tutorial.index', ['tutorial' => $materiTutorial->id_tutorial])
            ->with('success', 'Materi tutorial berhasil diperbarui.');
    }

    public function destroy(MateriTutorial $materiTutorial): RedirectResponse
    {
        $materiTutorial->delete();

        return redirect()->route('admin.materi-tutorial.index', ['tutorial' => $materiTutorial->id_tutorial])
            ->with('success', 'Materi tutorial berhasil dihapus.');
    }

    /**
     * @return array<int, string> [id_tutorial => judul], urut abjad agar mudah dicari
     */
    private function opsiTutorial(): array
    {
        return Tutorial::query()->orderBy('judul')->pluck('judul', 'id_tutorial')->all();
    }
}
