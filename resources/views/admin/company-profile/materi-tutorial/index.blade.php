@use('App\Support\TeksHtml')

@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Materi Tutorial' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.materi-tutorial.index')">
        <select name="tutorial" class="form-control kolom-filter" aria-label="Filter tutorial">
            <option value="">Semua tutorial</option>
            @foreach ($opsiTutorial as $id => $judul)
                <option value="{{ $id }}" @selected(request('tutorial') === (string) $id)>{{ $judul }}</option>
            @endforeach
        </select>
    </x-admin.filter-bar>

    <x-admin.card title="Materi Tutorial" :flush="true">
        <x-slot:aksi>
            {{-- Dari daftar yang sedang disaring per tutorial, form tambah langsung memilih tutorial itu. --}}
            <x-admin.tombol-tambah :href="route('admin.materi-tutorial.create', array_filter(['tutorial' => request('tutorial')]))" />
        </x-slot>

        @if ($materiTutorial->isEmpty())
            @if (request()->anyFilled(['q', 'tutorial']))
                <x-admin.empty-state title="Materi tutorial tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada materi tutorial" description="Materi adalah bagian berurutan dari sebuah tutorial." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align:center">Urutan</th>
                            <th class="kolom-lebar">Judul</th>
                            <th>Tutorial</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materiTutorial as $item)
                            <tr>
                                <td style="text-align:center" class="cell-strong">{{ $item->urutan_ke }}</td>
                                <td>
                                    <div class="cell-strong">{{ $item->judul }}</div>
                                    <div class="card-subtitle">{{ Str::limit(TeksHtml::polos($item->deskripsi), 120) }}</div>
                                </td>
                                <td>{{ $item->tutorial->judul }}</td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Materi Tutorial" :ikon-saja="true">
                                            {{-- Materi tanpa gambar: tabel rincian memakai lebar penuh modal. --}}
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Judul</th>
                                                            <td class="cell-strong">{{ $item->judul }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Tutorial</th>
                                                            <td>{{ $item->tutorial->judul }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Urutan ke</th>
                                                            <td>{{ $item->urutan_ke }}</td>
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
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.materi-tutorial.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.materi-tutorial.destroy', $item)" :ikon-saja="true"
                                            title="Hapus materi tutorial?"
                                            :message="'Materi “'.$item->judul.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($materiTutorial->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$materiTutorial" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
