@use('App\Support\TeksHtml')

@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Layanan' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.layanan.index')" />

    <x-admin.card title="Layanan" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.layanan.create')" />
        </x-slot>

        @if ($layanan->isEmpty())
            @if (request()->filled('q'))
                <x-admin.empty-state title="Layanan tidak ditemukan" description="Coba ubah kata kunci pencarian." />
            @else
                <x-admin.empty-state title="Belum ada layanan" description="Layanan tampil sebagai kartu di beranda website, sesuai urutannya." />
            @endif
        @else
            <x-admin.tabel>
                <thead>
                    <tr>
                        <th class="kolom-pas">Urutan</th>
                        <th class="kolom-pas">Gambar</th>
                        <th class="kolom-lebar">Judul</th>
                        <th class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($layanan as $item)
                        <tr>
                            <td class="kolom-pas cell-strong">{{ $item->urutan_ke }}</td>
                            <td class="kolom-pas"><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                            <td>
                                <div class="cell-strong">{{ $item->judul }}</div>
                                <div class="card-subtitle ringkasan-tabel">{{ Str::limit(TeksHtml::polos($item->deskripsi), 400) }}</div>
                            </td>
                            <x-admin.aksi-tabel>
                                <x-admin.detail-button title="Detail Layanan" :ikon-saja="true">
                                    <div class="detail-kolom">
                                        <x-admin.tabel-detail :model="$item">
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
                                        </x-admin.tabel-detail>
                                        <div class="pratinjau-gambar pratinjau-gambar-lebar">
                                            <img src="{{ $item->gambar_url }}" alt="Gambar layanan {{ $item->judul }}" loading="lazy">
                                        </div>
                                    </div>
                                </x-admin.detail-button>
                                <x-admin.edit-button :href="route('admin.layanan.edit', $item)" />
                                <x-admin.delete-button :action="route('admin.layanan.destroy', $item)" :ikon-saja="true"
                                    title="Hapus layanan?"
                                    :message="'Layanan “'.$item->judul.'” tidak akan tampil lagi di daftar maupun website.'" />
                            </x-admin.aksi-tabel>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.tabel>
        @endif

        @if ($layanan->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$layanan" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
