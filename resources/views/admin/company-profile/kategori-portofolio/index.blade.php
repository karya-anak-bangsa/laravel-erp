@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Kategori Portofolio' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.kategori-portofolio.index')" />

    <x-admin.card title="Kategori Portofolio" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.kategori-portofolio.create')" />
        </x-slot>

        @if ($kategoriPortofolio->isEmpty())
            @if (request()->filled('q'))
                <x-admin.empty-state title="Kategori portofolio tidak ditemukan" description="Coba ubah kata kunci pencarian." />
            @else
                <x-admin.empty-state title="Belum ada kategori portofolio" description="Kategori dipakai untuk mengelompokkan portofolio di website." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-lebar">Nama Kategori</th>
                            <th style="text-align:center">Jumlah Portofolio</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kategoriPortofolio as $item)
                            <tr>
                                <td class="cell-strong">{{ $item->nama_kategori }}</td>
                                <td style="text-align:center">{{ $item->portofolio_count }}</td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Kategori Portofolio" :ikon-saja="true">
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Nama Kategori</th>
                                                            <td class="cell-strong">{{ $item->nama_kategori }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Jumlah Portofolio</th>
                                                            <td>{{ $item->portofolio_count }}</td>
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
                                        <a href="{{ route('admin.kategori-portofolio.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.kategori-portofolio.destroy', $item)" :ikon-saja="true"
                                            title="Hapus kategori portofolio?"
                                            :message="$item->portofolio_count > 0
                                                ? 'Kategori “'.$item->nama_kategori.'” masih dipakai '.$item->portofolio_count.' portofolio sehingga tidak bisa dihapus.'
                                                : 'Kategori “'.$item->nama_kategori.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($kategoriPortofolio->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$kategoriPortofolio" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
