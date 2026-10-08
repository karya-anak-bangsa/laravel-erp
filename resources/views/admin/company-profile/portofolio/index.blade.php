@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Portofolio' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.portofolio.index')">
        <select name="kategori" class="form-control kolom-filter" aria-label="Filter kategori portofolio">
            <option value="">Semua kategori</option>
            @foreach ($daftarKategori as $kategori)
                <option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>
            @endforeach
        </select>
    </x-admin.filter-bar>

    <x-admin.card title="Portofolio" :flush="true">
        <x-slot:aksi>
            <a href="{{ route('admin.portofolio.create') }}" class="btn btn-success">
                <x-admin.icon name="plus" />
                Tambah Portofolio
            </a>
        </x-slot>

        @if ($portofolio->isEmpty())
            @if (request()->anyFilled(['q', 'kategori']))
                <x-admin.empty-state title="Portofolio tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada portofolio" description="Portofolio menampilkan hasil karya perusahaan di website.">
                    <a href="{{ route('admin.portofolio.create') }}" class="btn btn-success">
                        <x-admin.icon name="plus" />
                        Tambah Portofolio Pertama
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
                            <th>Kategori</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($portofolio as $item)
                            <tr>
                                <td><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                                <td>
                                    <div class="cell-strong">{{ $item->judul }}</div>
                                    <div class="card-subtitle">{{ Str::limit($item->deskripsi, 120) }}</div>
                                </td>
                                <td>{{ $item->kategori }}</td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Portofolio" :ikon-saja="true">
                                            <div class="detail-kolom">
                                                <div class="tabel-detail">
                                                    <table class="table tabel-informasi">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">Judul</th>
                                                                <td class="cell-strong">{{ $item->judul }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Kategori</th>
                                                                <td>{{ $item->kategori }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Alamat detail</th>
                                                                <td>/portofolio/{{ $item->slug }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Deskripsi</th>
                                                                <td class="teks-panjang">{{ $item->deskripsi }}</td>
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
                                                    <img src="{{ $item->gambar_url }}" alt="Gambar portofolio {{ $item->judul }}" loading="lazy">
                                                </div>
                                            </div>
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.portofolio.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.portofolio.destroy', $item)" :ikon-saja="true"
                                            title="Hapus portofolio?"
                                            :message="'Portofolio “'.$item->judul.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($portofolio->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$portofolio" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
