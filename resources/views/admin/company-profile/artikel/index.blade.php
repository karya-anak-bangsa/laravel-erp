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

    <x-admin.card title="Artikel" :flush="true">
        <x-slot:aksi>
            <a href="{{ route('admin.artikel.create') }}" class="btn btn-success">
                <x-admin.icon name="plus" />
                Tambah Artikel
            </a>
        </x-slot>

        @if ($artikel->isEmpty())
            @if (request()->anyFilled(['q', 'kategori', 'status']))
                <x-admin.empty-state title="Artikel tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada artikel" description="Artikel berstatus Terbit tampil di halaman artikel website.">
                    <a href="{{ route('admin.artikel.create') }}" class="btn btn-success">
                        <x-admin.icon name="plus" />
                        Tambah Artikel Pertama
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
                            <th>Tanggal</th>
                            <th style="text-align:center">Status</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($artikel as $item)
                            <tr>
                                <td><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                                <td>
                                    <div class="cell-strong">{{ $item->judul }}</div>
                                    <div class="card-subtitle">{{ Str::limit(TeksHtml::polos($item->deskripsi), 120) }}</div>
                                </td>
                                <td>{{ $item->kategoriArtikel?->nama_kategori ?? '—' }}</td>
                                <td style="white-space:nowrap">{{ $item->tanggal->translatedFormat('d F Y') }}</td>
                                <td style="text-align:center">
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
