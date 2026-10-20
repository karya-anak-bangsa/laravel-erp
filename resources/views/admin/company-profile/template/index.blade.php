@use('App\Enums\CompanyProfile\BayanganTemplate')
@use('App\Enums\CompanyProfile\JenisTemplate')

@extends('layouts.admin', ['breadcrumb' => ['Pengaturan Sistem' => null, 'Manajemen Template' => null]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.filter-bar :action="route('admin.template.index')">
        <select name="jenis" class="form-control kolom-filter" aria-label="Filter jenis template">
            <option value="">Semua jenis</option>
            @foreach (JenisTemplate::opsi() as $nilai => $label)
                <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="form-control kolom-filter" aria-label="Filter status template">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
    </x-admin.filter-bar>

    <x-admin.card title="Manajemen Template" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.template.create')" />
        </x-slot>

        @if ($template->isEmpty())
            @if (request()->anyFilled(['q', 'jenis', 'status']))
                <x-admin.empty-state title="Template tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada template" description="Template mengatur warna, huruf, dan bentuk website. Selama belum ada template aktif, website memakai tampilan bawaan." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-pas">Warna</th>
                            <th class="kolom-pas">Template</th>
                            <th class="kolom-pas">Jenis</th>
                            <th>Font</th>
                            <th class="kolom-status">Status</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($template as $item)
                            <tr>
                                <td class="kolom-pas">@include('admin.company-profile.template._contoh-warna', ['contoh' => $item])</td>
                                <td class="kolom-pas">
                                    <div class="cell-strong">{{ $item->nama }}</div>
                                    <div class="card-subtitle">Sudut {{ Str::lower($item->sudut->label()) }} · skala {{ Str::lower($item->skala->label()) }} · judul {{ Str::lower($item->ketebalan_judul->name) }} · {{ $item->bayangan === BayanganTemplate::Tanpa ? 'tanpa bayangan' : 'bayangan '.Str::lower($item->bayangan->label()) }}</div>
                                </td>
                                <td class="kolom-pas">{{ $item->jenis->label() }}</td>
                                <td>{{ $item->font->label() }}</td>
                                <td class="kolom-status">
                                    @if ($item->status_aktif)
                                        <span class="chip chip-green">Aktif</span>
                                    @else
                                        <span class="chip chip-red">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Template" :ikon-saja="true">
                                            {{-- Template tanpa gambar: tabel rincian memakai lebar penuh modal. --}}
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Nama</th>
                                                            <td class="cell-strong">{{ $item->nama }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Jenis</th>
                                                            <td>{{ $item->jenis->label() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Pengaturan</th>
                                                            <td>{{ $pengaturan[$item->getKey()]->label() }}</td>
                                                        </tr>
                                                        @if ($item->jenis === JenisTemplate::FullColor)
                                                            <tr>
                                                                <th scope="row">Warna utama</th>
                                                                <td><span class="contoh-template" aria-hidden="true"><span style="background: {{ $item->warna_utama }}"></span></span> {{ $item->warna_utama }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Warna aksen</th>
                                                                <td><span class="contoh-template" aria-hidden="true"><span style="background: {{ $item->warna_aksen }}"></span></span> {{ $item->warna_aksen }}</td>
                                                            </tr>
                                                        @else
                                                            <tr>
                                                                <th scope="row">Nada dasar</th>
                                                                <td>@include('admin.company-profile.template._contoh-warna', ['contoh' => $item]) {{ $item->nada_dasar?->label() }}</td>
                                                            </tr>
                                                        @endif
                                                        {{-- Urutan sama dengan form: huruf, bentuk, lalu ukuran. --}}
                                                        <tr>
                                                            <th scope="row">Font</th>
                                                            <td>{{ $item->font->label() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Ketebalan judul</th>
                                                            <td>{{ $item->ketebalan_judul->label() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Sudut</th>
                                                            <td>{{ $item->sudut->label() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Bayangan</th>
                                                            <td>{{ $item->bayangan->label() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Skala</th>
                                                            <td>{{ $item->skala->label() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Lebar konten</th>
                                                            <td>{{ $item->lebar_konten->label() }}</td>
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
                                                            <th scope="row">Pratinjau</th>
                                                            <td>
                                                                <a href="{{ route('admin.template.pratinjau', $item->nilaiTampilan()) }}" target="_blank" rel="noopener"
                                                                    class="btn btn-info btn-sm">
                                                                    <x-admin.icon name="eye" />
                                                                    Buka pratinjau
                                                                </a>
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
                                        <a href="{{ route('admin.template.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        @if ($item->status_aktif)
                                            {{-- Website harus selalu punya satu template aktif per jenis; server juga menolak. --}}
                                            <button type="button" class="btn btn-danger btn-ikon" disabled
                                                title="Template aktif tidak bisa dihapus" aria-label="Hapus (template aktif tidak bisa dihapus)">
                                                <x-admin.icon name="trash-can" />
                                            </button>
                                        @else
                                            <x-admin.delete-button :action="route('admin.template.destroy', $item)" :ikon-saja="true"
                                                title="Hapus template?"
                                                :message="'Template “'.$item->nama.'” tidak akan tampil lagi di daftar.'" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($template->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$template" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
