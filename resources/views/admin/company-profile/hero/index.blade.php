@use('App\Enums\CompanyProfile\GayaCta')
@use('App\Support\TeksHtml')

@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Hero' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.hero.index')">
        <select name="status" class="form-control kolom-filter" aria-label="Filter status hero">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
    </x-admin.filter-bar>

    <x-admin.card title="Hero" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.hero.create')" />
        </x-slot>

        @if ($hero->isEmpty())
            @if (request()->anyFilled(['q', 'status']))
                <x-admin.empty-state title="Hero tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada hero" description="Hero adalah bagian paling atas beranda: judul, deskripsi, gambar, dan tombol ajakan." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-pas">Gambar</th>
                            <th class="kolom-lebar">Judul</th>
                            <th class="kolom-sedang">Keyword</th>
                            <th class="kolom-status">Status</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hero as $item)
                            <tr>
                                <td class="kolom-pas"><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                                <td>
                                    <div class="cell-strong">{{ $item->judul }}</div>
                                    <div class="card-subtitle ringkasan-tabel">{{ Str::limit(TeksHtml::polos($item->deskripsi), 400) }}</div>
                                </td>
                                <td>{{ implode(', ', $item->keyword) }}</td>
                                <td class="kolom-status">
                                    @if ($item->status_aktif)
                                        <span class="chip chip-green">Aktif</span>
                                    @else
                                        <span class="chip">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Hero" :ikon-saja="true">
                                            <div class="detail-kolom">
                                                <div class="tabel-detail">
                                                    <table class="table tabel-informasi">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">Judul</th>
                                                                <td class="cell-strong">{{ $item->judul }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Deskripsi</th>
                                                                <td class="konten-html">{!! $item->deskripsi !!}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Keyword</th>
                                                                <td>{{ $item->keyword === [] ? '—' : implode(', ', $item->keyword) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Tombol CTA</th>
                                                                <td>
                                                                    @forelse ($item->cta as $cta)
                                                                        <div>{{ $cta['label'] }} → {{ $cta['url'] }} ({{ GayaCta::tryFrom($cta['gaya'])?->label() ?? $cta['gaya'] }})</div>
                                                                    @empty
                                                                        —
                                                                    @endforelse
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Status</th>
                                                                <td>
                                                                    @if ($item->status_aktif)
                                                                        <span class="chip chip-green">Aktif</span>
                                                                    @else
                                                                        <span class="chip">Nonaktif</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Dibuat</th>
                                                                <td>{{ $item->created_at->translatedFormat('d F Y, H:i') }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Diperbarui</th>
                                                                <td>{{ $item->updated_at->translatedFormat('d F Y, H:i') }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="pratinjau-gambar pratinjau-gambar-lebar">
                                                    <img src="{{ $item->gambar_url }}" alt="Gambar hero {{ $item->judul }}" loading="lazy">
                                                </div>
                                            </div>
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.hero.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.hero.destroy', $item)" :ikon-saja="true"
                                            title="Hapus hero?"
                                            :message="$item->status_aktif
                                                ? 'Hero “'.$item->judul.'” sedang tampil di beranda. Setelah dihapus, beranda tidak menampilkan hero sampai hero lain diaktifkan.'
                                                : 'Hero “'.$item->judul.'” tidak akan tampil lagi di daftar.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($hero->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$hero" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
