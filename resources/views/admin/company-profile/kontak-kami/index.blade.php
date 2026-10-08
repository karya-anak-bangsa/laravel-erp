@extends('layouts.admin', ['breadcrumb' => ['Company Profile' => null, 'Kontak Kami' => null]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <x-admin.filter-bar :action="route('admin.kontak-kami.index')">
        <select name="status" class="form-control kolom-filter" aria-label="Filter status baca pesan">
            <option value="">Semua status</option>
            <option value="belum-dibaca" @selected(request('status') === 'belum-dibaca')>Belum dibaca</option>
            <option value="dibaca" @selected(request('status') === 'dibaca')>Sudah dibaca</option>
        </select>
    </x-admin.filter-bar>

    {{-- Tanpa tombol Tambah: pesan masuk dari form kontak di website. --}}
    <x-admin.card title="Kontak Kami" :flush="true">
        @if ($kontakKami->isEmpty())
            @if (request()->anyFilled(['q', 'status']))
                <x-admin.empty-state title="Pesan tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada pesan masuk"
                    description="Pesan yang dikirim pengunjung lewat form kontak di website akan tampil di sini." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table tabel-kotak-masuk">
                    <thead>
                        <tr>
                            <th class="kolom-status" style="text-align:center">Status</th>
                            <th>Pengirim</th>
                            <th class="kolom-lebar">Subjek</th>
                            <th>Tanggal</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kontakKami as $item)
                            <tr @class(['belum-dibaca' => ! $item->status_baca])>
                                <td class="kolom-status" style="text-align:center">@include('admin.company-profile.kontak-kami._status')</td>
                                <td>
                                    <div class="teks-pesan">{{ $item->nama }}</div>
                                    <div class="card-subtitle">{{ $item->email }}</div>
                                </td>
                                <td>
                                    <div class="teks-pesan">{{ $item->subjek }}</div>
                                    <div class="card-subtitle">{{ Str::limit(Str::squish($item->pesan), 120) }}</div>
                                </td>
                                <td class="kolom-tanggal">{{ $item->tanggal->translatedFormat('d F Y, H:i') }}</td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        {{-- Pesan belum dibaca otomatis ditandai dibaca saat modal dibuka (resources/js/admin/kontak-kami.js). --}}
                                        <x-admin.detail-button title="Detail Pesan" :ikon-saja="true"
                                            :data-tandai-baca="$item->status_baca ? null : route('admin.kontak-kami.status-baca', $item)">
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Nama</th>
                                                            <td class="cell-strong">{{ $item->nama }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Email</th>
                                                            <td><a href="mailto:{{ $item->email }}?subject={{ rawurlencode('Re: '.$item->subjek) }}">{{ $item->email }}</a></td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Subjek</th>
                                                            <td>{{ $item->subjek }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Pesan</th>
                                                            <td class="teks-pesan-lengkap">{{ $item->pesan }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Diterima</th>
                                                            <td>{{ $item->tanggal->translatedFormat('d F Y, H:i') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Status</th>
                                                            <td>@include('admin.company-profile.kontak-kami._status')</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </x-admin.detail-button>
                                        <form method="POST" action="{{ route('admin.kontak-kami.status-baca', $item) }}" data-form-status-baca>
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status_baca" value="{{ $item->status_baca ? 0 : 1 }}">
                                            <button type="submit" class="btn btn-primary btn-ikon"
                                                title="{{ $item->status_baca ? 'Tandai belum dibaca' : 'Tandai dibaca' }}"
                                                aria-label="{{ $item->status_baca ? 'Tandai belum dibaca' : 'Tandai dibaca' }}">
                                                <x-admin.icon :name="$item->status_baca ? 'envelope' : 'envelope-open'" />
                                            </button>
                                        </form>
                                        <x-admin.delete-button :action="route('admin.kontak-kami.destroy', $item)" :ikon-saja="true"
                                            title="Hapus pesan?"
                                            :message="'Pesan dari “'.$item->nama.'” dengan subjek “'.$item->subjek.'” akan dihapus dari kotak masuk.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($kontakKami->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$kontakKami" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
