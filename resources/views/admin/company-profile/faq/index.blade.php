@use('App\Support\TeksHtml')

@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'FAQ' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.faq.index')" />

    <x-admin.card title="FAQ" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.faq.create')" />
        </x-slot>

        @if ($faq->isEmpty())
            @if (request()->filled('q'))
                <x-admin.empty-state title="FAQ tidak ditemukan" description="Coba ubah kata kunci pencarian." />
            @else
                <x-admin.empty-state title="Belum ada FAQ" description="FAQ tampil sebagai daftar tanya jawab di website, sesuai urutannya." />
            @endif
        @else
            <x-admin.tabel>
                <thead>
                    <tr>
                        <th class="kolom-pas">Urutan</th>
                        <th class="kolom-lebar">Pertanyaan</th>
                        <th class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($faq as $item)
                        <tr>
                            <td class="kolom-pas cell-strong">{{ $item->urutan_ke }}</td>
                            <td>
                                <div class="cell-strong">{{ $item->pertanyaan }}</div>
                                <div class="card-subtitle ringkasan-tabel">{{ Str::limit(TeksHtml::polos($item->jawaban), 400) }}</div>
                            </td>
                            <x-admin.aksi-tabel>
                                <x-admin.detail-button title="Detail FAQ" :ikon-saja="true">
                                    {{-- FAQ tanpa gambar: tabel rincian memakai lebar penuh modal. --}}
                                    <x-admin.tabel-detail :model="$item">
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
                                    </x-admin.tabel-detail>
                                </x-admin.detail-button>
                                <x-admin.edit-button :href="route('admin.faq.edit', $item)" />
                                <x-admin.delete-button :action="route('admin.faq.destroy', $item)" :ikon-saja="true"
                                    title="Hapus FAQ?"
                                    :message="'FAQ “'.$item->pertanyaan.'” tidak akan tampil lagi di daftar maupun website.'" />
                            </x-admin.aksi-tabel>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.tabel>
        @endif

        @if ($faq->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$faq" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
