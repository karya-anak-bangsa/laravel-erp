@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Layanan' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.layanan.index')" />

    <x-admin.card title="Layanan" :flush="true">
        <x-slot:aksi>
            <a href="{{ route('admin.layanan.create') }}" class="btn btn-success">
                <x-admin.icon name="plus" />
                Tambah Layanan
            </a>
        </x-slot>

        @if ($layanan->isEmpty())
            @if (request()->filled('q'))
                <x-admin.empty-state title="Layanan tidak ditemukan" description="Coba ubah kata kunci pencarian." />
            @else
                <x-admin.empty-state title="Belum ada layanan" description="Layanan tampil sebagai kartu di beranda website, sesuai urutannya.">
                    <a href="{{ route('admin.layanan.create') }}" class="btn btn-success">
                        <x-admin.icon name="plus" />
                        Tambah Layanan Pertama
                    </a>
                </x-admin.empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align:center">Urutan</th>
                            <th>Gambar</th>
                            <th class="kolom-lebar">Judul</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($layanan as $item)
                            <tr>
                                <td style="text-align:center" class="cell-strong">{{ $item->urutan_ke }}</td>
                                <td><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                                <td>
                                    <div class="cell-strong">{{ $item->judul }}</div>
                                    <div class="card-subtitle">{{ Str::limit($item->deskripsi, 120) }}</div>
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <a href="{{ route('admin.layanan.edit', $item) }}" class="btn btn-sm btn-warning">
                                            <x-admin.icon name="pen-to-square" />
                                            Ubah
                                        </a>
                                        <x-admin.delete-button :action="route('admin.layanan.destroy', $item)"
                                            title="Hapus layanan?"
                                            :message="'Layanan “'.$item->judul.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($layanan->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$layanan" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
