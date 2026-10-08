@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Kategori Artikel' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.kategori-artikel.index')" />

    <x-admin.card title="Kategori Artikel" :flush="true">
        <x-slot:aksi>
            <a href="{{ route('admin.kategori-artikel.create') }}" class="btn btn-success">
                <x-admin.icon name="plus" />
                Tambah Kategori
            </a>
        </x-slot>

        @if ($kategoriArtikel->isEmpty())
            @if (request()->filled('q'))
                <x-admin.empty-state title="Kategori artikel tidak ditemukan" description="Coba ubah kata kunci pencarian." />
            @else
                <x-admin.empty-state title="Belum ada kategori artikel" description="Kategori dipakai untuk mengelompokkan artikel di website.">
                    <a href="{{ route('admin.kategori-artikel.create') }}" class="btn btn-success">
                        <x-admin.icon name="plus" />
                        Tambah Kategori Pertama
                    </a>
                </x-admin.empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-lebar">Nama Kategori</th>
                            <th style="text-align:center">Jumlah Artikel</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kategoriArtikel as $item)
                            <tr>
                                <td class="cell-strong">{{ $item->nama_kategori }}</td>
                                <td style="text-align:center">{{ $item->artikel_count }}</td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Kategori Artikel" :ikon-saja="true">
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Nama Kategori</th>
                                                            <td class="cell-strong">{{ $item->nama_kategori }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Jumlah Artikel</th>
                                                            <td>{{ $item->artikel_count }}</td>
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
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.kategori-artikel.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.kategori-artikel.destroy', $item)" :ikon-saja="true"
                                            title="Hapus kategori artikel?"
                                            :message="$item->artikel_count > 0
                                                ? 'Kategori “'.$item->nama_kategori.'” masih dipakai '.$item->artikel_count.' artikel sehingga tidak bisa dihapus.'
                                                : 'Kategori “'.$item->nama_kategori.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($kategoriArtikel->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$kategoriArtikel" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
