@use('App\Support\TeksHtml')

@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Portofolio' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.portofolio.index')">
        <select name="kategori" class="form-control kolom-filter" aria-label="Filter kategori portofolio">
            <option value="">Semua kategori</option>
            @foreach ($opsiKategori as $id => $nama)
                <option value="{{ $id }}" @selected(request('kategori') === (string) $id)>{{ $nama }}</option>
            @endforeach
        </select>
    </x-admin.filter-bar>

    <x-admin.card title="Daftar Portofolio" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.portofolio.create')" />
        </x-slot>

        @if ($portofolio->isEmpty())
            @if (request()->anyFilled(['q', 'kategori']))
                <x-admin.empty-state title="Portofolio tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada portofolio" description="Portofolio menampilkan hasil karya perusahaan di website." />
            @endif
        @else
            <x-admin.tabel>
                <thead>
                    <tr>
                        <th class="kolom-pas">Gambar</th>
                        <th class="kolom-lebar">Judul</th>
                        <th class="kolom-pas">Kategori</th>
                        <th class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($portofolio as $item)
                        <tr>
                            <td class="kolom-pas"><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                            <td>
                                <div class="cell-strong">{{ $item->judul }}</div>
                                <div class="card-subtitle ringkasan-tabel">{{ Str::limit(TeksHtml::polos($item->deskripsi), 400) }}</div>
                            </td>
                            <td class="kolom-pas">{{ $item->kategoriPortofolio?->nama_kategori ?? '—' }}</td>
                            <x-admin.aksi-tabel>
                                <x-admin.detail-button title="Detail Portofolio" :ikon-saja="true">
                                    <div class="detail-kolom">
                                        <x-admin.tabel-detail :model="$item">
                                            <tr>
                                                <th scope="row">Judul</th>
                                                <td class="cell-strong">{{ $item->judul }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Kategori</th>
                                                <td>{{ $item->kategoriPortofolio?->nama_kategori ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Alamat detail</th>
                                                <td>/portofolio/{{ $item->slug }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Halaman</th>
                                                <td>
                                                    <a href="{{ route('portofolio.show', $item->slug) }}" target="_blank" rel="noopener"
                                                        class="btn btn-info btn-sm">
                                                        <x-admin.icon name="eye" />
                                                        Buka halaman
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Deskripsi</th>
                                                <td class="konten-html">{!! $item->deskripsi !!}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Gambar tambahan</th>
                                                <td>{{ $item->gambar_portofolio_count > 0 ? $item->gambar_portofolio_count.' gambar' : '—' }}</td>
                                            </tr>
                                        </x-admin.tabel-detail>
                                        <div class="pratinjau-gambar pratinjau-gambar-lebar">
                                            <img src="{{ $item->gambar_url }}" alt="Gambar portofolio {{ $item->judul }}" loading="lazy">
                                        </div>
                                    </div>
                                </x-admin.detail-button>
                                <x-admin.edit-button :href="route('admin.portofolio.edit', $item)" />
                                <x-admin.delete-button :action="route('admin.portofolio.destroy', $item)" :ikon-saja="true"
                                    title="Hapus portofolio?"
                                    :message="'Portofolio “'.$item->judul.'” tidak akan tampil lagi di daftar maupun website.'" />
                            </x-admin.aksi-tabel>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.tabel>
        @endif

        @if ($portofolio->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$portofolio" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
