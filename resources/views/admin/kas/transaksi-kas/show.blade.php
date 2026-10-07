@extends('layouts.admin', ['breadcrumb' => [
    'Kas Perusahaan' => null,
    'Transaksi Kas' => route('admin.transaksi-kas.index'),
    $transaksiKas->nomor_transaksi => null,
]])

@use('App\Support\FormatRupiah')

@php
    $buktiGambar = in_array(strtolower(pathinfo((string) $transaksiKas->bukti_transaksi, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
@endphp

@section('content')
    <x-admin.page-header :title="$transaksiKas->nomor_transaksi" pretitle="Transaksi Kas">
        <a href="{{ route('admin.transaksi-kas.index') }}" class="btn btn-outline">
            <x-admin.icon name="arrow-left" />
            Kembali
        </a>
        <a href="{{ route('admin.transaksi-kas.edit', $transaksiKas) }}" class="btn btn-warning">
            <x-admin.icon name="pen-to-square" />
            Ubah
        </a>
        <x-admin.delete-button :action="route('admin.transaksi-kas.destroy', $transaksiKas)"
            title="Hapus transaksi?"
            :message="'Transaksi '.$transaksiKas->nomor_transaksi.' tidak akan tampil lagi di daftar dan tidak dihitung di saldo.'" />
    </x-admin.page-header>

    <div class="row col-8-4">
        <x-admin.card title="Rincian Transaksi" :flush="true">
            <div class="table-responsive">
                <table class="table tabel-detail">
                    <tbody>
                        <tr>
                            <th>Nomor</th>
                            <td class="cell-mono">{{ $transaksiKas->nomor_transaksi }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $transaksiKas->tanggal_transaksi->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Jenis</th>
                            <td><span class="chip chip-{{ $transaksiKas->jenis_transaksi->warna() }}">{{ $transaksiKas->jenis_transaksi->label() }}</span></td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td>{{ $transaksiKas->kategoriTransaksi->nama_kategori }}</td>
                        </tr>
                        <tr>
                            <th>Akun Kas</th>
                            <td>{{ $transaksiKas->akunKas->nama_akun }}</td>
                        </tr>
                        <tr>
                            <th>Jumlah</th>
                            <td><span class="cell-mono cell-strong">{{ FormatRupiah::format($transaksiKas->jumlah) }}</span></td>
                        </tr>
                        <tr>
                            <th>Nama Pihak</th>
                            <td>{{ $transaksiKas->nama_pihak ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td style="white-space:pre-line">{{ $transaksiKas->keterangan }}</td>
                        </tr>
                        <tr>
                            <th>Dicatat</th>
                            <td>
                                {{ $transaksiKas->pembuat?->nama ?? '—' }}
                                <div class="card-subtitle">{{ $transaksiKas->created_at?->translatedFormat('d F Y, H:i') }}</div>
                            </td>
                        </tr>
                        <tr>
                            <th>Terakhir diubah</th>
                            <td>
                                {{ $transaksiKas->pengubah?->nama ?? '—' }}
                                <div class="card-subtitle">{{ $transaksiKas->updated_at?->translatedFormat('d F Y, H:i') }}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <x-admin.card title="Bukti Transaksi">
            @if ($transaksiKas->bukti_transaksi)
                @if ($buktiGambar)
                    <a href="{{ route('admin.transaksi-kas.bukti', $transaksiKas) }}" target="_blank" rel="noopener" title="Buka ukuran penuh">
                        <img src="{{ route('admin.transaksi-kas.bukti', $transaksiKas) }}" alt="Bukti transaksi {{ $transaksiKas->nomor_transaksi }}"
                            class="bukti-pratinjau" loading="lazy">
                    </a>
                @endif
                <a href="{{ route('admin.transaksi-kas.bukti', $transaksiKas) }}" target="_blank" rel="noopener" class="btn btn-outline">
                    <x-admin.icon :name="$buktiGambar ? 'up-right-from-square' : 'file-pdf'" />
                    {{ $buktiGambar ? 'Buka di tab baru' : 'Buka bukti (PDF)' }}
                </a>
            @else
                <x-admin.empty-state title="Belum ada bukti" description="Unggah foto atau scan nota lewat tombol Ubah." />
            @endif
        </x-admin.card>
    </div>
@endsection
