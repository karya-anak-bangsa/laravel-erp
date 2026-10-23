@use('App\Enums\CompanyProfile\StatusPublikasi')
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
            <x-admin.tabel>
                <thead>
                    <tr>
                        <th class="kolom-pas">Urutan</th>
                        <th class="kolom-lebar">Judul</th>
                        <th class="kolom-pas">Tutorial</th>
                        <th class="kolom-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($materiTutorial as $item)
                        <tr>
                            <td class="kolom-pas cell-strong">{{ $item->urutan_ke }}</td>
                            <td>
                                <div class="cell-strong">{{ $item->judul }}</div>
                                <div class="card-subtitle ringkasan-tabel">{{ Str::limit(TeksHtml::ringkasan($item->deskripsi), 400) }}</div>
                            </td>
                            <td class="kolom-pas">{{ $item->tutorial->judul }}</td>
                            <x-admin.aksi-tabel>
                                <x-admin.detail-button title="Detail Materi Tutorial" :ikon-saja="true">
                                    {{-- Materi tanpa gambar: tabel rincian memakai lebar penuh modal. --}}
                                    <x-admin.tabel-detail :model="$item">
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
                                            <th scope="row">Alamat detail</th>
                                            <td>/tutorial/{{ $item->tutorial->slug }}/{{ $item->slug }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Halaman</th>
                                            <td>
                                                {{-- Status ada di tingkat tutorial: materi tampil begitu tutorialnya terbit. --}}
                                                @if ($item->tutorial->status_publikasi === StatusPublikasi::Terbit)
                                                    <a href="{{ route('materi-tutorial.show', [$item->tutorial->slug, $item->slug]) }}" target="_blank" rel="noopener"
                                                        class="btn btn-info btn-sm">
                                                        <x-admin.icon name="eye" />
                                                        Buka halaman
                                                    </a>
                                                @else
                                                    Belum tampil (tutorial masih draf)
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Isi</th>
                                            <td class="konten-html">{!! $item->deskripsi !!}</td>
                                        </tr>
                                    </x-admin.tabel-detail>
                                </x-admin.detail-button>
                                <x-admin.edit-button :href="route('admin.materi-tutorial.edit', $item)" />
                                <x-admin.delete-button :action="route('admin.materi-tutorial.destroy', $item)" :ikon-saja="true"
                                    title="Hapus materi tutorial?"
                                    :message="'Materi “'.$item->judul.'” tidak akan tampil lagi di daftar maupun website.'" />
                            </x-admin.aksi-tabel>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.tabel>
        @endif

        @if ($materiTutorial->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$materiTutorial" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
