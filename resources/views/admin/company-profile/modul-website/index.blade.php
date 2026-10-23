@extends('layouts.admin', ['breadcrumb' => ['Pengaturan Sistem' => null, 'Manajemen Modul' => null]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    {{-- Tanpa kartu filter, tombol tambah, dan hapus: barisnya tetap satu per modul, hanya status yang diubah. --}}
    <x-admin.card title="Manajemen Modul" :flush="true">
        @if ($modulWebsite->isEmpty())
            <x-admin.empty-state title="Belum ada data modul" description="Selama belum ada data, semua modul tampil di website." />
        @else
            <x-admin.tabel>
                <thead>
                    <tr>
                        <th class="kolom-pas">Urutan</th>
                        <th class="kolom-lebar">Modul</th>
                        <th class="kolom-status">Status</th>
                        <th class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modulWebsite as $item)
                        <tr>
                            <td class="kolom-pas cell-strong">{{ $item->urutan_ke }}</td>
                            <td>
                                <div class="cell-strong">{{ $item->jenis->label() }}</div>
                                <div class="card-subtitle ringkasan-tabel">{{ implode(' · ', $item->jenis->tempatTampil()) }}</div>
                            </td>
                            <td class="kolom-status">
                                @if ($item->status_aktif)
                                    <span class="chip chip-green">Aktif</span>
                                @else
                                    <span class="chip chip-red">Nonaktif</span>
                                @endif
                            </td>
                            <x-admin.aksi-tabel>
                                <x-admin.detail-button title="Detail Modul" :ikon-saja="true">
                                    {{-- Modul tanpa gambar: tabel rincian memakai lebar penuh modal. --}}
                                    <x-admin.tabel-detail :model="$item">
                                        <tr>
                                            <th scope="row">Modul</th>
                                            <td class="cell-strong">{{ $item->jenis->label() }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Tampil di</th>
                                            <td class="konten-html">
                                                <ul>
                                                    @foreach ($item->jenis->tempatTampil() as $tempat)
                                                        <li>{{ $tempat }}</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Keterangan</th>
                                            <td>{{ $item->jenis->akibatNonaktif() }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Urutan</th>
                                            <td>{{ $item->urutan_ke }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Status</th>
                                            <td>
                                                @if ($item->status_aktif)
                                                    <span class="chip chip-green">Aktif</span>
                                                @else
                                                    <span class="chip chip-red">Nonaktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </x-admin.tabel-detail>
                                </x-admin.detail-button>
                                <x-admin.edit-button :href="route('admin.modul-website.edit', $item)" />
                            </x-admin.aksi-tabel>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.tabel>
        @endif
    </x-admin.card>
@endsection
