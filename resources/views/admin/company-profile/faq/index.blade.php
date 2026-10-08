@use('App\Support\TeksHtml')

@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'FAQ' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.faq.index')" />

    <x-admin.card title="FAQ" :flush="true">
        <x-slot:aksi>
            <a href="{{ route('admin.faq.create') }}" class="btn btn-success">
                <x-admin.icon name="plus" />
                Tambah FAQ
            </a>
        </x-slot>

        @if ($faq->isEmpty())
            @if (request()->filled('q'))
                <x-admin.empty-state title="FAQ tidak ditemukan" description="Coba ubah kata kunci pencarian." />
            @else
                <x-admin.empty-state title="Belum ada FAQ" description="FAQ tampil sebagai daftar tanya jawab di website, sesuai urutannya.">
                    <a href="{{ route('admin.faq.create') }}" class="btn btn-success">
                        <x-admin.icon name="plus" />
                        Tambah FAQ Pertama
                    </a>
                </x-admin.empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align:center">Urutan</th>
                            <th class="kolom-lebar">Pertanyaan</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($faq as $item)
                            <tr>
                                <td style="text-align:center" class="cell-strong">{{ $item->urutan_ke }}</td>
                                <td>
                                    <div class="cell-strong">{{ $item->pertanyaan }}</div>
                                    <div class="card-subtitle">{{ Str::limit(TeksHtml::polos($item->jawaban), 120) }}</div>
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail FAQ" :ikon-saja="true">
                                            {{-- FAQ tanpa gambar: tabel rincian memakai lebar penuh modal. --}}
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Pertanyaan</th>
                                                            <td class="cell-strong">{{ $item->pertanyaan }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Jawaban</th>
                                                            <td class="konten-html">{!! $item->jawaban !!}</td>
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
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.faq.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.faq.destroy', $item)" :ikon-saja="true"
                                            title="Hapus FAQ?"
                                            :message="'FAQ “'.$item->pertanyaan.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($faq->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$faq" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
