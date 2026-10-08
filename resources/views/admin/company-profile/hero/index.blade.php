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
            <a href="{{ route('admin.hero.create') }}" class="btn btn-success">
                <x-admin.icon name="plus" />
                Tambah Hero
            </a>
        </x-slot>

        @if ($hero->isEmpty())
            @if (request()->anyFilled(['q', 'status']))
                <x-admin.empty-state title="Hero tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada hero" description="Hero adalah bagian paling atas beranda: judul, deskripsi, gambar, dan tombol ajakan.">
                    <a href="{{ route('admin.hero.create') }}" class="btn btn-success">
                        <x-admin.icon name="plus" />
                        Tambah Hero Pertama
                    </a>
                </x-admin.empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th class="kolom-lebar">Judul</th>
                            <th class="kolom-sedang">Keyword</th>
                            <th style="text-align:center">Status</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hero as $item)
                            <tr>
                                <td><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                                <td>
                                    <div class="cell-strong">{{ $item->judul }}</div>
                                    <div class="card-subtitle">{{ Str::limit($item->deskripsi, 90) }}</div>
                                </td>
                                <td>{{ implode(', ', $item->keyword) }}</td>
                                <td style="text-align:center">
                                    @if ($item->status_aktif)
                                        <span class="status status-green">Aktif</span>
                                    @else
                                        <span class="status status-red">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <a href="{{ route('admin.hero.edit', $item) }}" class="btn btn-sm btn-warning">
                                            <x-admin.icon name="pen-to-square" />
                                            Ubah
                                        </a>
                                        <x-admin.delete-button :action="route('admin.hero.destroy', $item)"
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
