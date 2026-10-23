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
            <x-admin.tabel>
                <thead>
                    <tr>
                        <th class="kolom-pas">Nama Kategori</th>
                        <th class="kolom-jumlah">Jumlah Portofolio</th>
                        <th class="kolom-sisa" aria-hidden="true"></th>
                        <th class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kategoriPortofolio as $item)
                        <tr>
                            <td class="kolom-pas cell-strong">{{ $item->nama_kategori }}</td>
                            <td class="kolom-jumlah">{{ $item->portofolio_count }}</td>
                            <td class="kolom-sisa" aria-hidden="true"></td>
                            <x-admin.aksi-tabel>
                                <x-admin.detail-button title="Detail Kategori Portofolio" :ikon-saja="true">
                                    <x-admin.tabel-detail :model="$item">
                                        <tr>
                                            <th scope="row">Nama Kategori</th>
                                            <td class="cell-strong">{{ $item->nama_kategori }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Jumlah Portofolio</th>
                                            <td>{{ $item->portofolio_count }}</td>
                                        </tr>
                                    </x-admin.tabel-detail>
                                </x-admin.detail-button>
                                <x-admin.edit-button :href="route('admin.kategori-portofolio.edit', $item)" />
                                <x-admin.delete-button :action="route('admin.kategori-portofolio.destroy', $item)" :ikon-saja="true"
                                    title="Hapus kategori portofolio?"
                                    :message="$item->portofolio_count > 0
                                        ? 'Kategori “'.$item->nama_kategori.'” masih dipakai '.$item->portofolio_count.' portofolio sehingga tidak bisa dihapus.'
                                        : 'Kategori “'.$item->nama_kategori.'” tidak akan tampil lagi di daftar maupun website.'" />
                            </x-admin.aksi-tabel>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.tabel>
        @endif

        @if ($kategoriPortofolio->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$kategoriPortofolio" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
