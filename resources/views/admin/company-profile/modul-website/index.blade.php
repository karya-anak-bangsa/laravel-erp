@extends('layouts.admin', ['breadcrumb' => ['Pengaturan Sistem' => null, 'Manajemen Modul' => null]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    {{-- Tanpa kartu filter, tombol tambah, dan hapus: barisnya tetap satu per modul, hanya status yang diubah. --}}
    <x-admin.card title="Manajemen Modul" :flush="true">
        @if ($modulWebsite->isEmpty())
            <x-admin.empty-state title="Belum ada data modul" description="Selama belum ada data, semua modul tampil di website." />
        @else
            <div class="table-responsive">
                <table class="table">
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
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Modul" :ikon-saja="true">
                                            {{-- Modul tanpa gambar: tabel rincian memakai lebar penuh modal. --}}
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
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
                                        <a href="{{ route('admin.modul-website.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>
@endsection
