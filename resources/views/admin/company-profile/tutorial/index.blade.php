@use('App\Enums\CompanyProfile\JenisProgram')
@use('App\Enums\CompanyProfile\StatusPublikasi')
@use('App\Support\TeksHtml')

@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Tutorial' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.tutorial.index')">
        <select name="jenis" class="form-control kolom-filter" aria-label="Filter jenis program">
            <option value="">Semua jenis program</option>
            @foreach (JenisProgram::opsi() as $nilai => $label)
                <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="form-control kolom-filter" aria-label="Filter status tutorial">
            <option value="">Semua status</option>
            @foreach (StatusPublikasi::opsi() as $nilai => $label)
                <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
    </x-admin.filter-bar>

    <x-admin.card title="Tutorial" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.tutorial.create')" />
        </x-slot>

        @if ($tutorial->isEmpty())
            @if (request()->anyFilled(['q', 'jenis', 'status']))
                <x-admin.empty-state title="Tutorial tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada tutorial" description="Tutorial berisi materi berurutan, mis. Tutorial Laravel 13 dengan 8 materi." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th class="kolom-lebar">Judul</th>
                            <th>Jenis Program</th>
                            <th style="text-align:center">Jumlah Materi</th>
                            <th style="text-align:center">Status</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tutorial as $item)
                            <tr>
                                <td><img src="{{ $item->gambar_url }}" alt="" class="gambar-mini" loading="lazy"></td>
                                <td>
                                    <div class="cell-strong">{{ $item->judul }}</div>
                                    <div class="card-subtitle">{{ Str::limit(TeksHtml::polos($item->deskripsi), 120) }}</div>
                                </td>
                                <td style="white-space:nowrap">{{ $item->jenis_program->label() }}</td>
                                <td style="text-align:center">{{ $item->materi_tutorial_count }}</td>
                                <td style="text-align:center">
                                    <span class="status {{ $item->status_publikasi->kelasStatus() }}">{{ $item->status_publikasi->label() }}</span>
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Tutorial" :ikon-saja="true">
                                            <div class="detail-kolom">
                                                <div class="tabel-detail">
                                                    <table class="table tabel-informasi">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">Judul</th>
                                                                <td class="cell-strong">{{ $item->judul }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Jenis program</th>
                                                                <td>{{ $item->jenis_program->label() }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Status</th>
                                                                <td><span class="status {{ $item->status_publikasi->kelasStatus() }}">{{ $item->status_publikasi->label() }}</span></td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Jumlah materi</th>
                                                                <td>{{ $item->materi_tutorial_count }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Materi</th>
                                                                <td>
                                                                    <a href="{{ route('admin.materi-tutorial.index', ['tutorial' => $item->id_tutorial]) }}" class="btn btn-info btn-sm">
                                                                        <x-admin.icon name="list-ol" />
                                                                        Lihat materi
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Deskripsi</th>
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
                                                    <img src="{{ $item->gambar_url }}" alt="Gambar sampul {{ $item->judul }}" loading="lazy">
                                                </div>
                                            </div>
                                        </x-admin.detail-button>
                                        <a href="{{ route('admin.tutorial.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        <x-admin.delete-button :action="route('admin.tutorial.destroy', $item)" :ikon-saja="true"
                                            title="Hapus tutorial?"
                                            :message="$item->materi_tutorial_count > 0
                                                ? 'Tutorial “'.$item->judul.'” beserta '.$item->materi_tutorial_count.' materinya tidak akan tampil lagi di daftar maupun website.'
                                                : 'Tutorial “'.$item->judul.'” tidak akan tampil lagi di daftar maupun website.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($tutorial->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$tutorial" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
