@extends('layouts.admin', ['breadcrumb' => ['Kas Perusahaan' => null, 'Kategori Transaksi' => null]])

@use('App\Enums\Kas\JenisTransaksi')

@section('content')
    <x-admin.page-header title="Kategori Transaksi" pretitle="Kas Perusahaan">
        <a href="{{ route('admin.kategori-transaksi.create', request()->only('jenis')) }}" class="btn btn-primary">
            <x-admin.icon name="plus" />
            Tambah Kategori
        </a>
    </x-admin.page-header>

    <x-admin.card :flush="true">
        <x-admin.filter-bar :action="route('admin.kategori-transaksi.index')" placeholder="Cari nama kategori atau keterangan…">
            <select name="jenis" class="form-control" style="width:150px;height:32px" aria-label="Filter jenis transaksi">
                <option value="">Semua jenis</option>
                @foreach (JenisTransaksi::opsi() as $nilai => $teks)
                    <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $teks }}</option>
                @endforeach
            </select>
        </x-admin.filter-bar>

        @if ($kategoriTransaksi->isEmpty())
            @if (request()->anyFilled(['q', 'jenis']))
                <x-admin.empty-state title="Kategori transaksi tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada kategori transaksi" description="Kategori mengelompokkan pemasukan dan pengeluaran, mis. Jasa Pembuatan Website atau Domain & Hosting.">
                    <a href="{{ route('admin.kategori-transaksi.create') }}" class="btn btn-primary">
                        <x-admin.icon name="plus" />
                        Tambah Kategori Pertama
                    </a>
                </x-admin.empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Jenis</th>
                            <th>Keterangan</th>
                            <th style="text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kategoriTransaksi as $kategori)
                            <tr>
                                <td class="cell-strong">{{ $kategori->nama_kategori }}</td>
                                <td><span class="chip chip-{{ $kategori->jenis_transaksi->warna() }}">{{ $kategori->jenis_transaksi->label() }}</span></td>
                                <td>{{ Str::limit($kategori->keterangan ?? '—', 80) }}</td>
                                <td style="text-align:center;white-space:nowrap">
                                    <a href="{{ route('admin.kategori-transaksi.edit', $kategori) }}" class="btn btn-sm btn-warning">
                                        <x-admin.icon name="pen-to-square" />
                                        Ubah
                                    </a>
                                    <x-admin.delete-button :action="route('admin.kategori-transaksi.destroy', $kategori)"
                                        title="Hapus kategori transaksi?"
                                        :message="'Kategori “'.$kategori->nama_kategori.'” tidak akan tampil lagi di daftar.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($kategoriTransaksi->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$kategoriTransaksi" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
