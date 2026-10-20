@use('App\Enums\CompanyProfile\StatusPublikasi')
@use('App\Support\TeksHtml')

@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Artikel' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.artikel.index')">
        <select name="kategori" class="form-control kolom-filter" aria-label="Filter kategori artikel">
            <option value="">Semua kategori</option>
            @foreach ($opsiKategori as $id => $nama)
                <option value="{{ $id }}" @selected(request('kategori') === (string) $id)>{{ $nama }}</option>
            @endforeach
        </select>
        <select name="status" class="form-control kolom-filter" aria-label="Filter status artikel">
            <option value="">Semua status</option>
            @foreach (StatusPublikasi::opsi() as $nilai => $label)
                <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
    </x-admin.filter-bar>

    <x-admin.card title="Daftar Artikel" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.artikel.create')" />
        </x-slot>

        @if ($artikel->isEmpty())
            @if (request()->anyFilled(['q', 'kategori', 'status']))
                <x-admin.empty-state title="Artikel tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada artikel" description="Artikel berstatus Terbit tampil di halaman artikel website." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-pas">Gambar</th>
                            <th class="kolom-lebar">Judul</th>
                            <th class="kolom-pas">Kategori</th>
                            <th class="kolom-pas">Tanggal</th>
                            <th class="kolom-status">Status</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($artikel as $item)
                            <tr>
                                <td class="kolom-pas"><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                                <td>
                                    <div class="cell-strong">{{ $item->judul }}</div>
                                    <div class="card-subtitle ringkasan-tabel">{{ Str::limit(TeksHtml::ringkasan($item->deskripsi), 400) }}</div>
                                </td>
                                <td class="kolom-pas">{{ $item->kategoriArtikel?->nama_kategori ?? '—' }}</td>
                                <td class="kolom-pas">{{ $item->tanggal->translatedFormat('d F Y') }}</td>
                                <td class="kolom-status">
                                    <span class="status {{ $item->status_publikasi->kelasStatus() }}">{{ $item->status_publikasi->label() }}</span>
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Artikel" :ikon-saja="true">
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
                                                                <td>{{ $item->kategoriArtikel?->nama_kategori ?? '—' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Tanggal</th>
                                                                <td>{{ $item->tanggal->translatedFormat('d F Y') }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Status</th>
                                                                <td><span class="status {{ $item->status_publikasi->kelasStatus() }}">{{ $item->status_publikasi->label() }}</span></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Alamat detail</th>
                                                                <td>/artikel/{{ $item->slug }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Halaman</th>
                                                                <td>
                                                                    @if ($item->status_publikasi === StatusPublikasi::Terbit)
                                                                        <a href="{{ route('artikel.show', $item->slug) }}" target="_blank" rel="noopener"
                                                                            class="btn btn-info btn-sm">
                                                                            <x-admin.icon name="eye" />
                                                                            Buka halaman
                                                                        </a>
                                                                    @else
                                                                        Belum tampil (draf)
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Isi</th>
                                                                <td class="konten-html">{!! $item->deskripsi !!}</td>
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
                                                    <img src="{{ $item->gambar_url }}" alt="Gambar artikel {{ $item->judul }}" loading="lazy">
                                                </div>
                                            </div>
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.artikel.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.artikel.destroy', $item)" :ikon-saja="true"
                                            title="Hapus artikel?"
                                            :message="'Artikel “'.$item->judul.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($artikel->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$artikel" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
