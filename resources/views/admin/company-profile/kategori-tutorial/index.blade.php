@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Kategori Tutorial' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.kategori-tutorial.index')" />

    <x-admin.card title="Kategori Tutorial" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.kategori-tutorial.create')" />
        </x-slot>

        @if ($kategoriTutorial->isEmpty())
            @if (request()->filled('q'))
                <x-admin.empty-state title="Kategori tutorial tidak ditemukan" description="Coba ubah kata kunci pencarian." />
            @else
                <x-admin.empty-state title="Belum ada kategori tutorial" description="Kategori dipakai untuk mengelompokkan tutorial di website." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-lebar">Nama Kategori</th>
                            <th style="text-align:center">Jumlah Tutorial</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kategoriTutorial as $item)
                            <tr>
                                <td class="cell-strong">{{ $item->nama_kategori }}</td>
                                <td style="text-align:center">{{ $item->tutorial_count }}</td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Kategori Tutorial" :ikon-saja="true">
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Nama Kategori</th>
                                                            <td class="cell-strong">{{ $item->nama_kategori }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Jumlah Tutorial</th>
                                                            <td>{{ $item->tutorial_count }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Alamat halaman</th>
                                                            <td>/tutorial/kategori/{{ $item->slug }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Halaman</th>
                                                            <td>
                                                                {{-- Tutorial tayang = terbit dan punya minimal satu materi. --}}
                                                                @if ($item->tutorial_tayang_count > 0)
                                                                    <a href="{{ route('kategori-tutorial.show', $item) }}" target="_blank" rel="noopener"
                                                                        class="btn btn-info btn-sm">
                                                                        <x-admin.icon name="eye" />
                                                                        Buka halaman
                                                                    </a>
                                                                @else
                                                                    Belum tayang (belum ada tutorial tayang)
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
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.kategori-tutorial.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.kategori-tutorial.destroy', $item)" :ikon-saja="true"
                                            title="Hapus kategori tutorial?"
                                            :message="$item->tutorial_count > 0
                                                ? 'Kategori “'.$item->nama_kategori.'” masih dipakai '.$item->tutorial_count.' tutorial sehingga tidak bisa dihapus.'
                                                : 'Kategori “'.$item->nama_kategori.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($kategoriTutorial->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$kategoriTutorial" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
