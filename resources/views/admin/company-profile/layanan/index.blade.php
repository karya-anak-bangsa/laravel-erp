@use('App\Support\TeksHtml')

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
                                    <div class="card-subtitle">{{ Str::limit(TeksHtml::polos($item->deskripsi), 120) }}</div>
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Layanan" :ikon-saja="true">
                                            <div class="detail-kolom">
                                                <div class="tabel-detail">
                                                    <table class="table tabel-informasi">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">Judul</th>
                                                                <td class="cell-strong">{{ $item->judul }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Deskripsi</th>
                                                                <td class="konten-html">{!! $item->deskripsi !!}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Keterangan</th>
                                                                <td class="konten-html">{!! $item->keterangan ?: '—' !!}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Urutan ke</th>
                                                                <td>{{ $item->urutan_ke }}</td>
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
                                                    <img src="{{ $item->gambar_url }}" alt="Gambar layanan {{ $item->judul }}" loading="lazy">
                                                </div>
                                            </div>
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.layanan.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.layanan.destroy', $item)" :ikon-saja="true"
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
